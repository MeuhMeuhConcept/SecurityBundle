<?php

namespace Mmc\Security\Firewall;

use Mmc\Security\Authentication\Token\MmcToken;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\Firewall\AbstractListener;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\Security\Http\SecurityEvents;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class MmcJsonListener extends AbstractListener
{
    private $tokenStorage;
    private $authenticationManager;
    private $httpUtils;
    private $providerKey;
    private $successHandler;
    private $failureHandler;
    private $options;
    private $logger;
    private $eventDispatcher;
    private $sessionStrategy;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthenticationManagerInterface $authenticationManager,
        HttpUtils $httpUtils,
        string $providerKey,
        AuthenticationSuccessHandlerInterface $successHandler = null,
        AuthenticationFailureHandlerInterface $failureHandler = null,
        array $options = [],
        LoggerInterface $logger = null,
        EventDispatcherInterface $eventDispatcher = null
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->authenticationManager = $authenticationManager;
        $this->httpUtils = $httpUtils;
        $this->providerKey = $providerKey;
        $this->successHandler = $successHandler;
        $this->failureHandler = $failureHandler;
        $this->logger = $logger;
        $this->eventDispatcher = $eventDispatcher;
        $this->options = array_merge(['type_path' => 'type', 'key_path' => 'key'], $options);
    }

    public function supports(Request $request): ?bool
    {
        if (false === strpos($request->getRequestFormat(), 'json')
            && false === strpos($request->getContentType(), 'json')
        ) {
            return false;
        }

        if (isset($this->options['check_path']) && !$this->httpUtils->checkRequestPath($request, $this->options['check_path'])) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate(RequestEvent $event)
    {
        $request = $event->getRequest();
        $data = json_decode($request->getContent(), true);

        try {
            if (!is_array($data)) {
                throw new BadRequestHttpException('Invalid JSON.');
            }

            if (isset($data[$this->options['type_path']])) {
                $type = $data[$this->options['type_path']];
            } else {
                throw new BadRequestHttpException(sprintf('The key "%s" must be provided.', $this->options['type_path']));
            }

            if (isset($data[$this->options['key_path']])) {
                $key = $data[$this->options['key_path']];
            } else {
                throw new BadRequestHttpException(sprintf('The key "%s" must be provided.', $this->options['key_path']));
            }

            if (!\is_string($type)) {
                throw new BadRequestHttpException(sprintf('The key "%s" must be a string.', $this->options['type_path']));
            }

            if (!\is_string($key)) {
                throw new BadRequestHttpException(sprintf('The key "%s" must be a string.', $this->options['key_path']));
            }

            if (\strlen($key) > Security::MAX_USERNAME_LENGTH) {
                throw new BadCredentialsException('Invalid key.');
            }

            $token = new MmcToken($type, $key, $this->providerKey);

            unset($data[$this->options['type_path']]);
            unset($data[$this->options['key_path']]);

            $token->setExtras($data);

            $authenticatedToken = $this->authenticationManager->authenticate($token);
            $response = $this->onSuccess($request, $authenticatedToken);
        } catch (AuthenticationException $e) {
            $response = $this->onFailure($request, $e);
        } catch (BadRequestHttpException $e) {
            $request->setRequestFormat('json');

            throw $e;
        }

        if (null === $response) {
            return;
        }

        $event->setResponse($response);
    }

    private function onSuccess(Request $request, TokenInterface $token): ?Response
    {
        if (null !== $this->logger) {
            $this->logger->info('User has been authenticated successfully.', ['username' => $token->getUsername()]);
        }

        $this->tokenStorage->setToken($token);

        if (null !== $this->eventDispatcher) {
            $loginEvent = new InteractiveLoginEvent($request, $token);
            $this->eventDispatcher->dispatch($loginEvent, SecurityEvents::INTERACTIVE_LOGIN);
        }

        if (!$this->successHandler) {
            return null; // let the original request succeeds
        }

        $response = $this->successHandler->onAuthenticationSuccess($request, $token);

        if (!$response instanceof Response) {
            throw new \RuntimeException('Authentication Success Handler did not return a Response.');
        }

        return $response;
    }

    private function onFailure(Request $request, AuthenticationException $failed): Response
    {
        if (null !== $this->logger) {
            $this->logger->info('Authentication request failed.', ['exception' => $failed]);
        }

        $token = $this->tokenStorage->getToken();
        if ($token instanceof UsernamePasswordToken && $this->providerKey === $token->getProviderKey()) {
            $this->tokenStorage->setToken(null);
        }

        if (!$this->failureHandler) {
            return new JsonResponse(['error' => $failed->getMessageKey()], 401);
        }

        $response = $this->failureHandler->onAuthenticationFailure($request, $failed);

        if (!$response instanceof Response) {
            throw new \RuntimeException('Authentication Failure Handler did not return a Response.');
        }

        return $response;
    }
}
