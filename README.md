# Magento-Program-Update-Specials
This is a php program that automates the updating specials process on Magento. This program removes all products from the specials category and sub-categories, adds the new products that were imported from a CSV file, and automatically sets their special pricing (price, start date, and end date).

---

### README: Magento Specials and Subcategories Update Script

This script is designed to automate the process of updating product categories, promotional attributes, and special pricing in a Magento store. It performs two main tasks: **UpdateSpecials** (with special pricing) and **remove_and_add_specials_subcategories**.

---

#### **Prerequisites**
1. **Magento Environment**: Ensure that the Magento framework is properly initialized and accessible.
2. **PHP Configuration**: The script requires PHP with necessary extensions installed (e.g., `pdo_mysql`, `dom`, etc.).
3. **CSV File**: A CSV file named `Promotion Export.csv` must be placed at the specified path (`/var/www/html/Promotion Export.csv`). The CSV file should contain:
   - **Column 2** (index 1): Special price from date in format `YYYYMMDD` (e.g., `20250501`)
   - **Column 3** (index 2): Special price to date in format `YYYYMMDD` (e.g., `20250531`)
   - **Column 4** (index 3): Product SKU
   - **Column 6** (index 5): Special price amount
   
   If you save the file in another location, you need to update the file path in the script.
4. **Database Access**: The script directly interacts with the database, so ensure proper permissions for read/write operations.

---

#### **Script Overview**

##### **Part 1: UpdateSpecials**
This section handles the removal and addition of category ID 310, updates the `promotional_item` attribute, and sets special pricing for products.

1. **Get Products with Category ID 310**:
   - Retrieves all products currently assigned to category ID 310 (Specials category).
   
2. **Remove Category ID 310 and Reset Promotional Data**:
   - Removes category ID 310 from these products.
   - Updates the `promotional_item` attribute to `0` in the database.
   - **Clears special price, special from date, and special to date** to remove them from specials.

3. **Import CSV File**:
   - Reads the `Promotion Export.csv` file to extract product data including SKUs, special prices, and date ranges.

4. **Add Category ID 310 and Set Promotional Data**:
   - Adds category ID 310 to the products listed in the CSV file.
   - Updates the `promotional_item` attribute to `1` in the database.
   - **Sets the special price** from the CSV (column 6).
   - **Sets the special price from date** by converting the date format from `YYYYMMDD` to `YYYY-MM-DD` (column 2).
   - **Sets the special price to date** by converting the date format from `YYYYMMDD` to `YYYY-MM-DD` (column 3).

---

##### **Part 2: Remove and Add Specials Subcategories**
This section manages the removal and addition of subcategory IDs based on specific rules.

1. **Define Subcategory IDs to Remove**:
   - Specifies a list of subcategory IDs to be removed if category ID 310 is not present.

2. **Define Category Mappings**:
   - Maps primary category IDs to their corresponding subcategory IDs.

3. **Process Each Product**:
   - For each product:
     - Removes specified subcategory IDs if category ID 310 is absent.
     - Adds mapped subcategory IDs if category ID 310 is present.

---

#### **Execution Steps**
1. Place the script in the root directory of your Magento installation.
2. Ensure the `Promotion Export.csv` file is available at the specified path with the correct column structure.
3. Run the script using the command:
   ```bash
   php UpdateSpecials_UpdateSubcategories.php
   ```
   (If you rename the file use `php [FileName].php`)
4. Monitor the console output for progress and error messages.

---

#### **Key Features**
- **Dynamic Updates**: Automatically updates product categories and attributes based on predefined rules.
- **Special Price Automation**: Eliminates manual entry of special prices by reading pricing data directly from CSV and setting it in Magento's advanced pricing system.
- **Date Format Conversion**: Automatically converts date format from `YYYYMMDD` to Magento's required `YYYY-MM-DD` format.
- **Error Handling**: Catches and reports errors during execution.
- **Database Interaction**: Directly modifies the `catalog_product_entity_int` table for efficient updates.

---

#### **Configuration Details**
- **Attribute ID**: The `promotional_item` attribute is identified by its `attribute_id` (default: `196`). If this value differs in your setup, update it accordingly.
- **CSV File Path**: Adjust the `$csvFilePath` variable to point to the correct location of the CSV file.
- **CSV Column Structure**: Ensure your CSV file has the correct column structure:
  - Column 2: Special price from date (YYYYMMDD)
  - Column 3: Special price to date (YYYYMMDD)
  - Column 4: Product SKU
  - Column 6: Special price amount
- **Subcategory IDs**: Modify the `$subcategoryIdsToRemove` array to include or exclude specific subcategories.
- **Category Mappings**: Update the `$categoryMappings` array to reflect your store's category structure.

---

#### **Output**
- The script logs actions performed on each product, such as adding/removing categories, updating attributes, and setting special prices.
- Example log messages:
  ```
  Removed category ID 310 and set promo item to 0 for product SKU: ABC123
  Added category ID 310, set promo item to 1, and special price for product SKU: DEF456 (Price: 99.99, From: 2025-05-01, To: 2025-05-31)
  Removed category ID 282 from product SKU: GHI789
  Added category ID 311 to product SKU: JKL012
  ```

---

#### **Troubleshooting**
1. **CSV File Not Found**:
   - Verify the file path and ensure the file exists.
2. **Empty CSV File**:
   - Check the CSV file for data and ensure all required columns are present and correctly formatted.
3. **Date Format Issues**:
   - Ensure dates in the CSV are in `YYYYMMDD` format (e.g., `20250501`).
   - The script expects 8-character date strings.
4. **Special Price Not Updating**:
   - Verify that column indices in the script match your CSV structure.
   - Check that the special price column contains valid numeric values.
5. **Database Connection Issues**:
   - Confirm database credentials and permissions.
6. **Missing SKUs**:
   - The script logs missing SKUs with a message like `Can't find product with SKU: [SKU]`.

---

#### **Security Considerations**
- **Direct Database Access**: Be cautious when modifying database tables directly. Always back up your database before running the script.
- **File Permissions**: Ensure the script has appropriate permissions to read the CSV file and write logs.
- **Data Validation**: Verify CSV data integrity before running the script to prevent incorrect pricing or date assignments.

---

#### **Conclusion**
This script streamlines the process of managing promotional items, subcategories, and special pricing in Magento. By automating repetitive tasks including the manual entry of special prices and date ranges, it significantly reduces manual effort and minimizes the risk of errors. The automatic date conversion and pricing updates ensure consistency across your specials catalog. Regular testing and validation are recommended to ensure the script meets your store's requirements.
