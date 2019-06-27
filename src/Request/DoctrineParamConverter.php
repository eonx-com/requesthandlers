<?php
declare(strict_types=1);

namespace LoyaltyCorp\RequestHandlers\Request;

use InvalidArgumentException;
use LogicException;
use LoyaltyCorp\RequestHandlers\Exceptions\DoctrineParamConverterEntityNotFoundException;
use LoyaltyCorp\RequestHandlers\Exceptions\DoctrineParamConverterMisconfiguredException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\DoctrineParamConverter as BaseDoctrineParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class DoctrineParamConverter implements ParamConverterInterface
{
    /**
     * @var \Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\DoctrineParamConverter
     */
    private $instance;

    /**
     * Constructor
     *
     * @param \Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\DoctrineParamConverter $instance
     */
    public function __construct(BaseDoctrineParamConverter $instance)
    {
        $this->instance = $instance;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\DoctrineParamConverterEntityNotFoundException
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\DoctrineParamConverterMisconfiguredException
     */
    public function apply(Request $request, ParamConverter $configuration): bool
    {
        try {
            $result = $this->instance->apply($request, $configuration);
        } catch (InvalidArgumentException $exception) {
            // Misconfigured annotation, unknown options provided
            throw new DoctrineParamConverterMisconfiguredException(
                'DoctrineParamConverter is misconfigured. ' . $exception->getMessage(),
                null,
                null,
                $exception
            );
        } catch (LogicException $exception) {
            // Misconfigured annotation, unable to resolve how to retrieve an entity
            throw new DoctrineParamConverterMisconfiguredException(
                'DoctrineParamConverter is misconfigured. ' . $exception->getMessage(),
                null,
                null,
                $exception
            );
        } catch (NotFoundHttpException $exception) {
            // Didnt find anything, 404
            throw new DoctrineParamConverterEntityNotFoundException(
                \sprintf('%s could not be found', $configuration->getName()),
                ['class' => $configuration->getClass()],
                $configuration->getClass(),
                $configuration->getName(),
                null,
                $exception
            );
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ParamConverter $configuration): bool
    {
        return $this->instance->supports($configuration);
    }
}
