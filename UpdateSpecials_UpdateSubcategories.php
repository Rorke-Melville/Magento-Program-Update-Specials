<?php
/*
Auther: Rorke Melville
Update Specials, Update Subcategories & Update Promo Items

Part 1
1. Get all products with category ID 310
2. Remove category ID 310 from the products and set promotional_item to 0
3. Import the CSV file
4. Get all products from the CSV file based on SKU
5. Add category ID 310 to the products from the CSV file and set promotional_item to 1
Part 2
- Define the subcategory IDs to remove
- Define the category mappings for adding new subcategory IDs
1. Remove specified subcategory IDs if category ID 310 is not present
2. Add specified category mappings
*/
use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\State;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\File\Csv;
use Magento\Framework\App\ResourceConnection;

require __DIR__ . '/app/bootstrap.php';

// Initialize the Magento framework only once
$bootstrap = Bootstrap::create(BP, $_SERVER);
$objectManager = $bootstrap->getObjectManager();
$state = $objectManager->get(State::class);
$state->setAreaCode('adminhtml');

// Get necessary Magento services
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
$collectionFactory = $objectManager->get(CollectionFactory::class);
$csvProcessor = $objectManager->get(Csv::class);
$resource = $objectManager->get(ResourceConnection::class);
$connection = $resource->getConnection();

$attributeId = 196; // promotional_item attribute_id

// START: UpdateSpecials logic
try {
    // 1. Get all products with category ID 310
    $productCollection = $collectionFactory->create();
    $productCollection->addAttributeToSelect(['sku', 'category_ids']);
    $productCollection->addCategoriesFilter(['eq' => 310]);

    // Store products in an array
    $productsWithCategory310 = [];
    foreach ($productCollection as $product) {
        $productsWithCategory310[] = $product;
    }

    // 2. Remove category ID 310 from the products and set promotional_item to 0
    foreach ($productsWithCategory310 as $product) {
        $existingCategoryIds = $product->getCategoryIds();
        if (in_array(310, $existingCategoryIds)) {
            $existingCategoryIds = array_diff($existingCategoryIds, [310]);
            $product->setCategoryIds(array_values($existingCategoryIds));

            // Set promotional_item to 0 by updating attribute_id directly in the database
            $connection->update(
                $resource->getTableName('catalog_product_entity_int'),
                ['value' => 0],
                [
                    'attribute_id = ?' => $attributeId,
                    'entity_id = ?' => $product->getId()
                ]
            );

            $productRepository->save($product);
            echo "Removed category ID 310 and set promo item to 0 for product SKU: {$product->getSku()}\n";
        }
    }
    echo "All products removed successfully.\n";

    // 3. Import the CSV file
    $csvFilePath = '/var/www/html/ace/sftp/Promotion Export.csv'; // Ensure path & file name is correct
    if (!file_exists($csvFilePath)) {
        throw new Exception("CSV file not found: $csvFilePath");
    }

    // Read CSV file
    $csvData = $csvProcessor->getData($csvFilePath);
    if (empty($csvData)) {
        throw new Exception("CSV file is empty or invalid.");
    }

    // 4. Get all products from the CSV file based on SKU
    $skusFromCsv = array_column($csvData, 3); // Assuming the SKU is in column 4 of CSV file
    $csvProducts = [];
    foreach ($skusFromCsv as $sku) {
        try{
        $product = $productRepository->get($sku);
        if ($product) {
            $csvProducts[] = $product;
        } 
        }
        catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            echo "Can't find product with SKU: $sku\n"; // Log the missing SKU
        }
    }

    // 5. Add category ID 310 to the products from the CSV file and set promotional_item to 1
    foreach ($csvProducts as $product) {
        $existingCategoryIds = $product->getCategoryIds();
        if (!in_array(310, $existingCategoryIds)) {
            $existingCategoryIds[] = 310; // Add category ID 310
            $product->setCategoryIds(array_unique($existingCategoryIds));

            // Set promotional_item to 1 by updating attribute_id directly in the database
            $connection->update(
                $resource->getTableName('catalog_product_entity_int'),
                ['value' => 1],
                [
                    'attribute_id = ?' => $attributeId,
                    'entity_id = ?' => $product->getId()
                ]
            );

            $productRepository->save($product);
            echo "Added category ID 310 and set promo item to 1 for product SKU: {$product->getSku()}\n";
        }
    }

    echo "UpdateSpecials process completed successfully.\n";
} catch (Exception $e) {
    echo "Error in UpdateSpecials: " . $e->getMessage() . "\n";
}
// END: UpdateSpecials logic

// START: remove_and_add_specials_subcategories logic
try {
    // Define the subcategory IDs to remove
    $subcategoryIdsToRemove = array_merge(
        [282, 283, 284, 285, 286, 305, 287, 288, 289, 290, 291, 292, 293, 294, 295, 296, 297, 298, 299, 300, 301, 302, 304], // CATEGORY SPECIFIC
        [311, 312, 313, 314, 315, 316, 333, 317, 318, 319, 320, 321, 322, 323, 324, 325, 326, 327, 328, 329, 330, 331, 332]  // SPECIALS
    );

    // Define the category mappings for adding new subcategory IDs
    $categoryMappings = [
        '28'  => [282, 311],  // Adhesives
        '63'  => [283, 312],  // Bathroom
        '19'  => [284, 313],  // Bedroom
        '21'  => [285, 314],  // Kitchen
        '24'  => [286, 315],  // Castors
        '26'  => [287, 316],  // Connection Fittings
        '15'  => [288, 333],  // Door Sets
        '102' => [289, 317],  // Edging
        '249' => [290, 318],  // Electricals
        '45'  => [291, 319],  // Fasteners
        '252' => [292, 320],  // Furniture Hardware
        '5'   => [293, 321],  // Handles
        '7'   => [294, 322],  // Hinges
        '49'  => [295, 323],  // Legs
        '253' => [296, 324],  // Staples & Nails
        '43'  => [297, 325],  // Runners
        '241' => [298, 326],  // Sanding & Painting
        '71'  => [299, 327],  // Shelving & Brackets
        '60'  => [300, 328],  // Sinks
        '12'  => [301, 329],  // Tools
        '84'  => [302, 330],  // Taps
        '53'  => [303, 331],  // Locks
        '17'  => [304, 332]   // Office Furniture Fittings
    ];

    // Load all products
    $productCollection = $collectionFactory->create();
    $productCollection->addAttributeToSelect(['sku', 'category_ids']);

    foreach ($productCollection as $product) {
        $existingCategoryIds = $product->getCategoryIds();
        $originalCategoryIds = $existingCategoryIds;

        // Part 1: Remove specified subcategory IDs if category ID 310 is not present
        if (!in_array(310, $existingCategoryIds)) {
            foreach ($subcategoryIdsToRemove as $subcategoryId) {
                if (in_array($subcategoryId, $existingCategoryIds)) {
                    $existingCategoryIds = array_diff($existingCategoryIds, [$subcategoryId]);
                    echo "Removed category ID $subcategoryId from product SKU: {$product->getSku()}\n";
                }
            }
        }

        // Part 2: Add specified category mappings
        // Check if category ID 310 is present
        if (in_array(310, $existingCategoryIds)) {
            foreach ($categoryMappings as $keyCategoryId => $newCategoryIds) {
                if (in_array($keyCategoryId, $existingCategoryIds)) {
                    foreach ($newCategoryIds as $newCategoryId) {
                        if (!in_array($newCategoryId, $existingCategoryIds)) {
                            $existingCategoryIds[] = $newCategoryId;
                            echo "Added category ID $newCategoryId to product SKU: {$product->getSku()}\n";
                        }
                    }
                }
            }
        }

        // Update the product's category IDs only if changes were made
        if ($existingCategoryIds !== $originalCategoryIds) {
            $product->setCategoryIds(array_unique($existingCategoryIds));
            $productRepository->save($product);
        }
    }

    echo "Specials subcategories process completed successfully.\n";
} catch (\Exception $e) {
    echo "Error in remove_and_add_specials: " . $e->getMessage() . "\n";
}
// END: remove_and_add_specials logic
echo "Specials Updated Successfully. \n";
?>
