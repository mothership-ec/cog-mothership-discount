<?php

namespace Message\Mothership\Discount\Form\BundleProductSelector;

use Message\Mothership\Discount\Bundle;
use Symfony\Component\Form;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints;

class ProductSelectorGroupForm extends Form\AbstractType
{
	const PRODUCT_ROW = 'product_row_';

	private $_productSelector;

	public function __construct(ProductSelectorForm $productSelector)
	{
		$this->_productSelector = $productSelector;
	}

	public function getName()
	{
		return 'bundle_product_selector_group';
	}

	public function buildForm(Form\FormBuilderInterface $builder, array $options)
	{
		$this->_validateOptions($options);

		foreach ($options['bundle']->getProductRows() as $productRow) {

			if (empty($options['units'][$productRow->getID()])) {
				throw new \LogicException('No units assigned to product row ' . $productRow->getID());
			}

			if (empty($options['products'][$productRow->getProductID()])) {
				throw new \LogicException('Product with ID `' . $productRow->getProductID() . '` not set on form');
			}

			for ($i = 0; $i < $productRow->getQuantity(); ++$i) {
				$builder->add(self::PRODUCT_ROW . $productRow->getID() . '_' . $i, $this->_productSelector, [
					'units'        => $options['units'][$productRow->getID()],
					'unit_options' => $productRow->getOptions(),
					'out_of_stock' => $options['out_of_stock'],
					'product'      => $options['products'][$productRow->getProductID()],
				]);
			}
		}
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults([
			'bundle' => null,
			'units'  => [],
			'out_of_stock' => [],
			'products' => [],
		]);
	}

	private function _validateOptions(array $options)
	{
		if (empty($options['bundle'])) {
			throw new \LogicException('Bundle not set in options');
		}

		if (!$options['bundle'] instanceof Bundle\Bundle) {
			$type = gettype($options['bundle']) === 'object' ? get_class($options['bundle']) : gettype($options['bundle']);

			throw new \InvalidArgumentException('`bundle` must be an instance of Bundle, ' . $type . ' given');
		}

		if (empty($options['products']) || !is_array($options['products'])) {
			throw new \LogicException('Products must be set and must be an array');
		}

		if (empty($options['units']) || !is_array($options['units'])) {
			throw new \LogicException('Units must be set and must be an array');
		}
	}
}