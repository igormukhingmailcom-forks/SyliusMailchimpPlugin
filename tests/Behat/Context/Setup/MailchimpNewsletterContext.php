<?php

declare(strict_types=1);

namespace Tests\Setono\SyliusMailchimpPlugin\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Setono\SyliusMailchimpPlugin\Entity\MailchimpConfigInterface;
use Setono\SyliusMailchimpPlugin\Entity\MailchimpExportInterface;
use Setono\SyliusMailchimpPlugin\Repository\MailchimpConfigRepositoryInterface;
use Sylius\Behat\Page\Shop\Account\LoginPageInterface;
use Sylius\Behat\Service\SecurityServiceInterface;
use Sylius\Behat\Service\SharedStorageInterface;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\ShopUserInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\User\Repository\UserRepositoryInterface;
use Tests\Setono\SyliusMailchimpPlugin\Behat\Service\RandomStringGeneratorInterface;

final class MailchimpNewsletterContext implements Context
{
    /** @var SharedStorageInterface */
    private $sharedStorage;

    /** @var ChannelRepositoryInterface */
    private $channelRepository;

    /** @var MailchimpConfigRepositoryInterface */
    private $mailChimpConfigRepository;

    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var LoginPageInterface */
    private $loginPage;

    /** @var FactoryInterface */
    private $userFactory;

    /** @var FactoryInterface */
    private $customerFactory;

    /** @var SecurityServiceInterface */
    private $securityService;

    /** @var FactoryInterface */
    private $configFactory;

    /** @var RandomStringGeneratorInterface */
    private $randomStringGenerator;

    /** @var FactoryInterface */
    private $mailChimpExportFactory;

    public function __construct(
        SharedStorageInterface $sharedStorage,
        ChannelRepositoryInterface $channelRepository,
        MailchimpConfigRepositoryInterface $mailChimpConfigRepository,
        UserRepositoryInterface $userRepository,
        LoginPageInterface $loginPage,
        FactoryInterface $userFactory,
        FactoryInterface $customerFactory,
        SecurityServiceInterface $securityService,
        FactoryInterface $configFactory,
        RandomStringGeneratorInterface $randomStringGenerator,
        FactoryInterface $mailChimpExportFactory
    ) {
        $this->sharedStorage = $sharedStorage;
        $this->channelRepository = $channelRepository;
        $this->mailChimpConfigRepository = $mailChimpConfigRepository;
        $this->userRepository = $userRepository;
        $this->loginPage = $loginPage;
        $this->userFactory = $userFactory;
        $this->customerFactory = $customerFactory;
        $this->securityService = $securityService;
        $this->configFactory = $configFactory;
        $this->randomStringGenerator = $randomStringGenerator;
        $this->mailChimpExportFactory = $mailChimpExportFactory;
    }

    /**
     * @Given the Mailchimp config is set up
     */
    public function theMailchimpConfigIsSetUp(): void
    {
        /** @var MailchimpConfigInterface $config */
        $config = $this->createConfig();

        $this->saveConfig($config);
    }

    /**
     * @Given the store allows all emails to be exported
     */
    public function theStoreAllowsAllEmailsToBeExported(): void
    {
        $this->mailChimpConfigRepository->findConfig()->setExportAll(true);
    }

    /**
     * @Given the :email customer is subscribed to the newsletter
     */
    public function theCustomerIsSubscribedToTheNewsletter(string $email): void
    {
        /** @var ShopUserInterface $user */
        $user = $this->sharedStorage->get('user');

        $user->getCustomer()->setSubscribedToNewsletter(true);
        $this->sharedStorage->set('user', $user);
    }

    /**
     * @Given this email is also subscribed to the default Mailchimp list
     */
    public function thisEmailIsAlsoSubscribedToTheDefaultMailchimpList(): void
    {
        /** @var MailchimpExportInterface $mailChimpExport */
        $mailChimpExport = $this->mailChimpExportFactory->createNew();

        /** @var ShopUserInterface $user */
        $user = $this->sharedStorage->get('user');

        /** @var CustomerInterface $customer */
        $customer = $user->getCustomer();

        $mailChimpExport->addCustomer($customer);
    }

    private function createConfig(
        ?string $code = null,
        ?string $apiKey = null
    ): MailchimpConfigInterface {
        /** @var MailchimpConfigInterface $config */
        $config = $this->configFactory->createNew();

        $config->setCode($code ?? $this->randomStringGenerator->generate(10));
        $config->setApiKey($apiKey ?? $this->randomStringGenerator->generate(10));

        return $config;
    }

    private function saveConfig(MailchimpConfigInterface $config): void
    {
        $this->mailChimpConfigRepository->add($config);

        $this->sharedStorage->set('config', $config);
    }
}
