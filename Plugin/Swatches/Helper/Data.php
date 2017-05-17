<?php
namespace METMEER\TextSwatchLabelFix\Plugin\Swatches\Helper;

use \Magento\Swatches\Model\Swatch;

class Data extends \Magento\Swatches\Helper\Data
{
	/**
	 * Get swatch options by option id's according to fallback logic
	 *
	 * @param array $optionIds
	 * @return array
	 */
	public function getSwatchesByOptionsId(array $optionIds)
	{
		/** @var \Magento\Swatches\Model\ResourceModel\Swatch\Collection $swatchCollection */
		$swatchCollection = $this->swatchCollectionFactory->create();
		$swatchCollection->addFilterByOptionsIds($optionIds);

		$swatches = [];
		$currentStoreId = $this->storeManager->getStore()->getId();
		foreach ($swatchCollection as $item) {
			if ($item['type'] != Swatch::SWATCH_TYPE_TEXTUAL) {
				$swatches[$item['option_id']] = $item->getData();
			} elseif ($item['store_id'] == $currentStoreId && $item['value']) {
				$fallbackValues[$item['option_id']][$currentStoreId] = $item->getData();
			} elseif ($item->getData()) {
				$fallbackValues[$item['option_id']][self::DEFAULT_STORE_ID] = $item->getData();
			}
		}

		if (!empty($fallbackValues)) {
			$swatches = $this->addFallbackOptions($fallbackValues, $swatches);
		}

		return $swatches;
	}


	/**
	 * @param array $fallbackValues
	 * @param array $swatches
	 * @return array
	 */
	protected function addFallbackOptions(array $fallbackValues, array $swatches)
	{
		$currentStoreId = $this->storeManager->getStore()->getId();
		foreach ($fallbackValues as $optionId => $optionsArray) {
			if (isset($optionsArray[$currentStoreId])) {
				$swatches[$optionId] = $optionsArray[$currentStoreId];
			} else {
				$swatches[$optionId] = $optionsArray[self::DEFAULT_STORE_ID];
			}
		}

		return $swatches;
	}
}