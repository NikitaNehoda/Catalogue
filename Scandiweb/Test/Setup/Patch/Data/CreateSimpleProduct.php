<?php
namespace Scandiweb\Test\Setup\Patch\Data;

use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\App\State;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory;

use Magento\InventoryApi\Api\SourceItemsSaveInterface;


class CreateSimpleProduct implements DataPatchInterface
{
    protected ModuleDataSetupInterface $setup;
    protected ProductInterfaceFactory $productInterfaceFactory;
    protected ProductRepositoryInterface $productRepository;
    protected State $appState;
	protected EavSetup $eavSetup;
    protected StoreManagerInterface $storeManager;
	protected SourceItemInterfaceFactory $sourceItemFactory;
	protected SourceItemsSaveInterface $sourceItemsSaveInterface;
	protected CategoryLinkManagementInterface $categoryLink;
    protected array $sourceItems = [];

    public function __construct(
        ModuleDataSetupInterface $setup,
        ProductInterfaceFactory $productInterfaceFactory,
        ProductRepositoryInterface $productRepository,
        State $appState,
        StoreManagerInterface $storeManager,
        EavSetup $eavSetup,
		SourceItemInterfaceFactory $sourceItemFactory,
        SourceItemsSaveInterface $sourceItemsSaveInterface,
		CategoryLinkManagementInterface $categoryLink
    ) {
        $this->appState = $appState;
        $this->productInterfaceFactory = $productInterfaceFactory;
        $this->productRepository = $productRepository;
        $this->setup = $setup;
		$this->eavSetup = $eavSetup;
        $this->storeManager = $storeManager;
        $this->sourceItemFactory = $sourceItemFactory;
        $this->sourceItemsSaveInterface = $sourceItemsSaveInterface;
		$this->categoryLink = $categoryLink;
    }
	
    public function apply()
    {
        $this->appState->emulateAreaCode('adminhtml', [$this, 'execute']);
    }

    public function execute()
    {
        $product = $this->productInterfaceFactory->create();

        $attributeSetId = $this->eavSetup->getAttributeSetId(Product::ENTITY, 'Default');

        $sourceItems = [
            [
                'sku'               => 'test',
                'name'              => 'Some Product',
                'attribute_id'      => $attributeSetId,
                'status'            => Status::STATUS_ENABLED,
                'price'             => 100,
                'visibility'        => Visibility::VISIBILITY_BOTH,
                'type_id'           => Type::TYPE_SIMPLE,
            ]
        ];
        
        foreach($sourceItems as $item) {
            $product->setSku($item['sku'])
                ->setName($item['name'])
                ->setAttributeSetId($item['attribute_id'])
                ->setStatus($item['status'])
                ->setPrice($item['price'])
                ->setVisibility($item['visibility'])
                ->setTypeId($item['type_id']);

            $product = $this->productRepository->save($product);
        }
    }

    public function getAliases(){
        return [];
    }
    
    public static function getDependencies()
    {
        return[];
    }
}
