<?php
declare(strict_types=1);

namespace LoyaltyCorp\RequestHandlers\Bridge\Laravel\Providers;

use DateTime;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Persistence\ManagerRegistry;
use EoneoPay\Utils\AnnotationReader;
use FOS\RestBundle\Serializer\SymfonySerializerAdapter;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;
use LoyaltyCorp\RequestHandlers\Request\RequestBodyParamConverter;
use LoyaltyCorp\RequestHandlers\Serializer\DoctrineDenormalizer;
use LoyaltyCorp\RequestHandlers\Serializer\PropertyNormalizer;
use LoyaltyCorp\RequestHandlers\Serializer\RequestBodySerializer;
use Sensio\Bundle\FrameworkExtraBundle\EventListener\ControllerListener;
use Sensio\Bundle\FrameworkExtraBundle\EventListener\ParamConverterListener;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\DoctrineParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterManager;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\DateIntervalNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Validator\Constraints\DivisibleByValidator;
use Symfony\Component\Validator\Constraints\EqualToValidator;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqualValidator;
use Symfony\Component\Validator\Constraints\GreaterThanValidator;
use Symfony\Component\Validator\Constraints\IdenticalToValidator;
use Symfony\Component\Validator\Constraints\LessThanOrEqualValidator;
use Symfony\Component\Validator\Constraints\LessThanValidator;
use Symfony\Component\Validator\Constraints\NotEqualToValidator;
use Symfony\Component\Validator\Constraints\NotIdenticalToValidator;
use Symfony\Component\Validator\ConstraintValidatorFactoryInterface;
use Symfony\Component\Validator\ContainerConstraintValidatorFactory;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ParamConverterProvider extends ServiceProvider
{
    /**
     * @noinspection PhpMissingParentCallCommonInspection
     *
     * {@inheritdoc}
     */
    public function register(): void
    {
        $validators = [
            DivisibleByValidator::class,
            EqualToValidator::class,
            GreaterThanOrEqualValidator::class,
            GreaterThanValidator::class,
            IdenticalToValidator::class,
            LessThanOrEqualValidator::class,
            LessThanValidator::class,
            NotEqualToValidator::class,
            NotIdenticalToValidator::class
        ];

        foreach ($validators as $validator) {
            $this->app->bind($validator);
        }

        $this->app->alias(AnnotationReader::class, Reader::class);
        $this->app->singleton(
            ClassMetadataFactoryInterface::class,
            static function (Container $app): ClassMetadataFactory {
                $loader = new AnnotationLoader($app->make(AnnotationReader::class));

                return new ClassMetadataFactory($loader);
            }
        );
        $this->app->singleton(
            ConstraintValidatorFactoryInterface::class,
            static function (Container $app): ContainerConstraintValidatorFactory {
                return new ContainerConstraintValidatorFactory($app);
            }
        );
        $this->app->singleton(ControllerListener::class);
        $this->app->singleton(
            DoctrineParamConverter::class,
            static function (Container $app): DoctrineParamConverter {
                return new DoctrineParamConverter(
                    $app->make(ManagerRegistry::class),
                    $app->make(ExpressionLanguage::class)
                );
            }
        );
        $this->app->singleton(PropertyAccessorInterface::class, static function (): PropertyAccessor {
            return new PropertyAccessor(true);
        });
        $this->app->singleton(
            RequestBodyParamConverter::class,
            static function (Container $app): RequestBodyParamConverter {
                $serializer = $app->make('requesthandlers_serializer');

                // Note: we're intentionally not using the Validation component in this
                // ParamConverter so we can customise the validation to occur at a later time
                return new RequestBodyParamConverter(
                    new SymfonySerializerAdapter($serializer)
                );
            }
        );
        $this->app->singleton(ParamConverterListener::class);
        $this->app->singleton(
            ParamConverterManager::class,
            static function (Container $app): ParamConverterManager {
                $manager = new ParamConverterManager();
                $manager->add($app->make(DoctrineParamConverter::class), 5);
                $manager->add($app->make(RequestBodyParamConverter::class), 1);

                return $manager;
            }
        );
        $this->app->singleton(ValidatorInterface::class, static function (Container $app): ValidatorInterface {
            $reader = $app->make(AnnotationReader::class);
            $constraintFactory = $app->make(ConstraintValidatorFactoryInterface::class);

            $validator = Validation::createValidatorBuilder()
                ->enableAnnotationMapping($reader)
                ->setConstraintValidatorFactory($constraintFactory)
                ->getValidator();

            return $validator;
        });

        $this->app->singleton('requesthandlers_serializer', static function (Container $app): RequestBodySerializer {
            $reflectionExtractor = new ReflectionExtractor();
            $phpDocExtractor = new PhpDocExtractor();

            $normalizers = [
                new DoctrineDenormalizer($app->make(ManagerRegistry::class)),
                new PropertyNormalizer(
                    $app->make(ClassMetadataFactoryInterface::class),
                    new CamelCaseToSnakeCaseNameConverter(),
                    new PropertyInfoExtractor(
                        [$reflectionExtractor],
                        [$reflectionExtractor, $phpDocExtractor],
                        [$phpDocExtractor],
                        [$reflectionExtractor],
                        [$reflectionExtractor]
                    )
                ),
                new ArrayDenormalizer(),
                new DateTimeNormalizer([
                    DateTimeNormalizer::FORMAT_KEY => DateTime::RFC3339
                ]), // TODO: wrap or reimplement so a better exception is thrown
                new DateIntervalNormalizer([
                    DateIntervalNormalizer::FORMAT_KEY => 'P%mM'
                ]) // TODO: wrap or reimplement so a better exception is thrown
            ];

            $encoders = [
                new JsonEncoder(),
                new XmlEncoder()
            ];

            return new RequestBodySerializer($normalizers, $encoders);
        });
    }
}
