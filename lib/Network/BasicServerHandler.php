<?php


namespace Nicodinus\UmeyeApi\Core\Network;


use Amp\Delayed;
use Amp\Loop;
use Amp\Promise;
use Amp\Socket\Socket;
use Amp\Socket\SocketAddress;
use Amp\Socket\SocketException;
use Amp\Success;
use Amp\Sync\LocalMutex;
use Amp\Sync\Lock;
use Amp\Sync\Mutex;
use Nicodinus\Amp\Extensions\Network\SocketServer;
use Nicodinus\Amp\Extensions\Runnable\AsyncRunnableTrait;
use function Amp\asyncCall;
use function Amp\call;

abstract class BasicServerHandler implements ServerHandlerInterface
{
    use AsyncRunnableTrait;

    /**
     * @var SocketServer
     */
    protected SocketServer $socketServerHandler;

    /**
     * @var ClientSessionInterface[]
     */
    protected array $sessions = [];

    /**
     * @var Mutex
     */
    protected Mutex $mutex;

    /**
     * @var string|null
     */
    protected ?string $sessionsWatcher;

    /**
     * @var float
     */
    protected float $socketCLoseInactivityDelaySeconds = 5;

    /**
     * BasicServerHandler constructor.
     * @param string $listenUri
     * @throws SocketException
     * @throws \LogicException
     */
    public function __construct(string $listenUri)
    {
        $this->socketServerHandler = SocketServer::create($listenUri);
        $this->mutex = new LocalMutex();
    }

    /**
     * @param Socket $socket
     * @return Promise<ClientSessionInterface>
     */
    public function initSession(Socket &$socket): Promise
    {
        $peer = $socket->getRemoteAddress();

        $peerStr = $peer->toString();

        if (isset($this->sessions[$peerStr])) {
            return new Success($this->sessions[$peerStr]);
        }

        return call(function (self &$self, Socket &$socket) use (&$peerStr) {

            /** @var Lock $lock */
            $lock = yield $self->mutex->acquire();

            if (!$self->hasSession($socket->getRemoteAddress())) {
                $self->sessions[$peerStr] = yield $self->createSessionInstance($socket);
                dump("A new session started with {$peerStr}!");
            }

            $lock->release();
            unset($lock);

            return $self->sessions[$peerStr];

        }, $this, $socket);
    }

    /**
     * @param SocketAddress $peer
     * @return bool
     */
    public function hasSession(SocketAddress $peer): bool
    {
        return isset($this->sessions[$peer->toString()]);
    }

    /**
     * @param SocketAddress $peer
     * @return ClientSessionInterface
     */
    public function &getSession(SocketAddress $peer): ClientSessionInterface
    {
        $peerStr = $peer->toString();

        if (!isset($this->sessions[$peerStr])) {
            throw new \LogicException("Session {$peerStr} not found!");
        }

        return $this->sessions[$peerStr];
    }

    /**
     * @return Promise<void>
     */
    protected function onRunningStart(): Promise
    {
        return call(function (self &$self) {

            yield $self->socketServerHandler->appendAcceptor(function (Socket &$socket) use (&$self) {

                asyncCall(function (self &$self, Socket &$socket) {

                    /** @var ClientSessionInterface $clientSession */
                    $clientSession = null;

                    if (!$self->hasSession($socket->getRemoteAddress())) {
                        $clientSession = yield $self->initSession($socket);
                    } else {
                        $clientSession = $self->getSession($socket->getRemoteAddress());
                    }

                    yield $clientSession->handleRx();

                }, $this, $socket);

            });

            $self->sessionsWatcher = Loop::repeat(1000, function () use (&$self) {

                /** @var Lock $lock */
                $lock = yield $self->mutex->acquire();

                $sessions = [];
                foreach ($self->sessions as $clientSession) {
                    if (!$clientSession->getSocket()->isClosed()) {
                        if ($self->socketCLoseInactivityDelaySeconds <= 0) {
                            $sessions[] = $clientSession;
                        } else {
                            $activityDiff = microtime(true) - $clientSession->getLastActivityTime();
                            if ($activityDiff < $self->socketCLoseInactivityDelaySeconds) {
                                $sessions[] = $clientSession;
                            } else {
                                dump("Session {$clientSession->getSocket()->getRemoteAddress()->toString()} is pending to remove due an inactivity!");
                                $clientSession->getSocket()->close();
                            }
                        }
                    } else {
                        dump("Session {$clientSession->getSocket()->getRemoteAddress()->toString()} is pending to remove due an closed state!");
                    }
                }

                $self->sessions = $sessions;

                $lock->release();
                unset($lock);

            });

            $self->socketServerHandler->run()->onResolve(function (?\Throwable $e = null) use (&$self) {

                if (!empty($e)) {
                    $self->runningTickException = $e;
                    return;
                }

                Promise\wait($self->gracefulShutdown());
            });

        }, $this);
    }

    /**
     * @return Promise<void>
     */
    protected function onRunningTick(): Promise
    {
        return new Delayed(1000);
    }

    /**
     * @return Promise<void>
     */
    protected function onRunningEnd(): Promise
    {
        return call(function (self &$self) {

            if (!empty($self->sessionsWatcher)) {
                Loop::cancel($self->sessionsWatcher);
                $self->sessionsWatcher = null;
            }

            /** @var Lock $lock */
            $lock = yield $self->mutex->acquire();

            $self->sessions = [];

            $lock->release();
            unset($lock);

        }, $this);
    }

    /**
     * @return Promise<mixed>
     */
    protected function onShutdownPending(): Promise
    {
        return call(function (self &$self) {

            if (!empty($self->sessionsWatcher)) {
                Loop::cancel($self->sessionsWatcher);
                $self->sessionsWatcher = null;
            }

            return yield $self->socketServerHandler->gracefulShutdown();

        }, $this);
    }

    /**
     * @param Socket $socket
     * @return Promise<ClientSessionInterface>
     */
    protected abstract function createSessionInstance(Socket &$socket): Promise;
}