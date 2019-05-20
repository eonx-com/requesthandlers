<?php /** @noinspection PhpMissingParentCallCommonInspection */
declare(strict_types=1);

namespace LoyaltyCorp\RequestHandlers\Event;

use LoyaltyCorp\RequestHandlers\Exceptions\KernelNotAvailableException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent as BaseFilterControllerEvent;

/**
 * Overridden to enable ParamConverterListener from Sensio's FrameworkExtraBundle to work
 * without modification.
 *
 * This event will not fire correctly for some event listeners because we modify the
 * constructor and do not provide everything that is required.
 */
class FilterControllerEvent extends BaseFilterControllerEvent
{
    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    private $request;

    /**
     * Constructor
     *
     * @noinspection MagicMethodsValidityInspection PhpMissingParentConstructorInspection
     *
     * @param callable $controller
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function __construct(callable $controller, Request $request)
    {
        $this->setController($controller);
        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\KernelNotAvailableException
     */
    public function getKernel()
    {
        throw new KernelNotAvailableException(
            'Kernel is not available when using the LoyaltyCorp/RequestHandlers library'
        );
    }

    /**
     * @noinspection SenselessMethodDuplicationInspection
     *
     * {@inheritdoc}
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestType(): int
    {
        return 1;
    }

    /**
     * {@inheritdoc}
     */
    public function isMasterRequest(): bool
    {
        return true;
    }
}
