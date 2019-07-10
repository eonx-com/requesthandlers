<?php
declare(strict_types=1);

namespace LoyaltyCorp\RequestHandlers\Request;

use LoyaltyCorp\RequestHandlers\Exceptions\ParamConverterMisconfiguredException;
use LoyaltyCorp\RequestHandlers\Request\Interfaces\ParamConverterManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * This class implements similar functionality to the Sensio FrameworkExtra bundle's
 * ParamConverterManager.
 *
 * The primary purpose of this class it to apply configurated param converters against
 * a supplied request.
 *
 * Reasons for adding our own:
 * - Added an interface (so it can be stubbed in our customised ParamConverterListener)
 * - Throw an exception when a configuration is not processed by any param converters.
 */
class ParamConverterManager implements ParamConverterManagerInterface
{
    /**
     * Converters sorted by priority
     *
     * @var \Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface[][]
     */
    private $converters = [];

    /**
     * Converters that have explicit names
     *
     * @var \Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface[]
     */
    private $namedConverters = [];

    /**
     * Adds a parameter converter.
     *
     * Converters match either explicitly via $name or by iteration over all
     * converters with a $priority. If you pass a $priority = null then the
     * added converter will not be part of the iteration chain and can only
     * be invoked explicitly.
     *
     * @param \Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface $converter
     * @param int|null $priority
     * @param string|null $name
     *
     * @return void
     */
    public function add(ParamConverterInterface $converter, ?int $priority, ?string $name): void
    {
        if (\array_key_exists($priority, $this->converters) === false) {
            $this->converters[$priority] = [];
        }

        $this->converters[$priority][] = $converter;

        if ($name !== null) {
            $this->namedConverters[$name] = $converter;
        }
    }

    /**
     * Returns all registered param converters.
     *
     * @return \Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface[]
     */
    public function all(): array
    {
        \krsort($this->converters);

        return \array_merge(...$this->converters);
    }

    /**
     * Applies all converters to the passed configurations and stops when a
     * converter is applied it will move on to the next configuration and so on.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationInterface[] $configurations
     *
     * @return void
     *
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\ParamConverterMisconfiguredException
     */
    public function apply(Request $request, array $configurations): void
    {
        foreach ($configurations as $configuration) {
            $this->applyConverter($request, $configuration);
        }
    }

    /**
     * Applies converter on request based on the given configuration.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter $configuration
     *
     * @return void
     *
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\ParamConverterMisconfiguredException
     */
    private function applyConverter(Request $request, ParamConverter $configuration): void
    {
        $value = $request->attributes->get($configuration->getName());
        $className = $configuration->getClass();

        // If the value is already an instance of the class we are trying to convert it into
        // we should continue as no conversion is required
        if (\is_object($value) && $value instanceof $className) {
            return;
        }

        if ($configuration->getConverter() !== null) {
            $this->runNamedConverter($configuration, $request);

            return;
        }

        foreach ($this->all() as $converter) {
            if ($converter->supports($configuration) !== true) {
                continue;
            }

            if ($converter->apply($request, $configuration) === true) {
                return;
            }
        }

        // If we got here, no converters supported the configuration.
        throw new ParamConverterMisconfiguredException(\sprintf(
            'The ParamConverter for "%s" was not processed by any known converters.',
            $configuration->getName()
        ));
    }

    /**
     * Finds and runs a named converter against the configuration.
     *
     * @param \Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter $configuration
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return void
     *
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\ParamConverterMisconfiguredException
     */
    private function runNamedConverter(ParamConverter $configuration, Request $request): void
    {
        $name = $configuration->getConverter();

        if (\array_key_exists($name, $this->namedConverters) === false) {
            throw new ParamConverterMisconfiguredException(\sprintf(
                'No converter named "%s" found for conversion of parameter "%s".',
                $name,
                $configuration->getName()
            ));
        }

        $converter = $this->namedConverters[$name];

        if ($converter->supports($configuration) === false) {
            throw new ParamConverterMisconfiguredException(\sprintf(
                'Converter "%s" does not support conversion of parameter "%s".',
                $name,
                $configuration->getName()
            ));
        }

        if ($converter->apply($request, $configuration) === true) {
            return;
        }

        // If we got here, the converter didnt not support apply the result.
        throw new ParamConverterMisconfiguredException(\sprintf(
            'The converter "%s" did not run for property "%s".',
            $name,
            $configuration->getName()
        ));
    }
}
