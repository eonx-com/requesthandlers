<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\Bridge\Laravel\Providers;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Persistence\ManagerRegistry as CommonManagerRegistry;
use Doctrine\Persistence\ManagerRegistry;
use EoneoPay\Utils\AnnotationReader;
use EoneoPay\Utils\Interfaces\AnnotationReaderInterface;
use LoyaltyCorp\RequestHandlers\Bridge\Laravel\Providers\ParamConverterProvider;
use LoyaltyCorp\RequestHandlers\Builder\Interfaces\ObjectBuilderInterface;
use LoyaltyCorp\RequestHandlers\Builder\ObjectBuilder;
use LoyaltyCorp\RequestHandlers\Request\CurrentDateTimeConverter;
use LoyaltyCorp\RequestHandlers\Request\DoctrineParamConverter;
use LoyaltyCorp\RequestHandlers\Request\Interfaces\ContextConfiguratorInterface;
use LoyaltyCorp\RequestHandlers\Request\RequestBodyParamConverter;
use LoyaltyCorp\RequestHandlers\Serializer\Interfaces\DoctrineDenormalizerEntityFinderInterface;
use LoyaltyCorp\RequestHandlers\Serializer\RequestBodySerializer;
use Sensio\Bundle\FrameworkExtraBundle\EventListener\ControllerListener;
use Sensio\Bundle\FrameworkExtraBundle\EventListener\ParamConverterListener;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\DoctrineParamConverter as RealDoctrineParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterManager;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Validator\Constraints\DivisibleByValidator;
use Symfony\Component\Validator\Constraints\EqualToValidator;
use Symfony\Component\Validator\Constraints\ExpressionValidator;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqualValidator;
use Symfony\Component\Validator\Constraints\GreaterThanValidator;
use Symfony\Component\Validator\Constraints\IdenticalToValidator;
use Symfony\Component\Validator\Constraints\LessThanOrEqualValidator;
use Symfony\Component\Validator\Constraints\LessThanValidator;
use Symfony\Component\Validator\Constraints\NotEqualToValidator;
use Symfony\Component\Validator\Constraints\NotIdenticalToValidator;
use Symfony\Component\Validator\ConstraintValidatorFactoryInterface;
use Symfony\Component\Validator\ContainerConstraintValidatorFactory;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Tests\LoyaltyCorp\RequestHandlers\Stubs\Request\ContextConfiguratorStub;
use Tests\LoyaltyCorp\RequestHandlers\Stubs\Serializer\DoctrineDenormalizerEntityFinderStub;
use Tests\LoyaltyCorp\RequestHandlers\Stubs\Vendor\Doctrine\Common\Persistence\ManagerRegistryStub;
use Tests\LoyaltyCorp\RequestHandlers\Stubs\Vendor\Illuminate\Contracts\Foundation\ApplicationStub;
use Tests\LoyaltyCorp\RequestHandlers\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects) Coupling required to fully test service provider
 */
class ParamConverterProviderTest extends TestCase
{
    /**
     * Tests register
     *
     * @return void
     */
    public function testRegister(): void
    {
        $application = new ApplicationStub();
        $registry = new ManagerRegistryStub();
        $application->instance(ManagerRegistry::class, $registry);
        $application->instance(CommonManagerRegistry::class, $registry);
        $application->bind(AnnotationReaderInterface::class, AnnotationReader::class);

        $application->bind(
            DoctrineDenormalizerEntityFinderInterface::class,
            DoctrineDenormalizerEntityFinderStub::class
        );

        // Register services
        (new ParamConverterProvider($application))->register();

        $services = [
            'requesthandlers_serializer' => RequestBodySerializer::class,
            'validator.expression' => ExpressionValidator::class,
            Reader::class => AnnotationReader::class,
            ClassMetadataFactoryInterface::class => ClassMetadataFactory::class,
            ConstraintValidatorFactoryInterface::class => ContainerConstraintValidatorFactory::class,
            ControllerListener::class => ControllerListener::class,
            CurrentDateTimeConverter::class => CurrentDateTimeConverter::class,
            DivisibleByValidator::class => DivisibleByValidator::class,
            EqualToValidator::class => EqualToValidator::class,
            GreaterThanOrEqualValidator::class => GreaterThanOrEqualValidator::class,
            GreaterThanValidator::class => GreaterThanValidator::class,
            IdenticalToValidator::class => IdenticalToValidator::class,
            LessThanOrEqualValidator::class => LessThanOrEqualValidator::class,
            LessThanValidator::class => LessThanValidator::class,
            NotEqualToValidator::class => NotEqualToValidator::class,
            NotIdenticalToValidator::class => NotIdenticalToValidator::class,
            ObjectBuilderInterface::class => ObjectBuilder::class,
            PropertyAccessorInterface::class => PropertyAccessor::class,
            RequestBodyParamConverter::class => RequestBodyParamConverter::class,
            ParamConverterListener::class => ParamConverterListener::class,
            ParamConverterManager::class => ParamConverterManager::class,
            RealDoctrineParamConverter::class => DoctrineParamConverter::class,
            ValidatorInterface::class => ValidatorInterface::class
        ];

        foreach ($services as $abstract => $concrete) {
            // Ensure services are bound
            self::assertInstanceOf($concrete, $application->get($abstract));
        }
    }
    /**
     * Tests register
     *
     * @return void
     */
    public function testContextConfigurator(): void
    {
        $application = new ApplicationStub();
        $registry = new ManagerRegistryStub();
        $application->instance(ManagerRegistry::class, $registry);
        $application->instance(CommonManagerRegistry::class, $registry);
        $application->bind(AnnotationReaderInterface::class, AnnotationReader::class);
        $application->bind(ContextConfiguratorInterface::class, ContextConfiguratorStub::class);

        $application->bind(
            DoctrineDenormalizerEntityFinderInterface::class,
            DoctrineDenormalizerEntityFinderStub::class
        );

        // Register services
        (new ParamConverterProvider($application))->register();

        $application->get(RequestBodyParamConverter::class);

        // Service was created successfully
        $this->addToAssertionCount(1);
    }
}
