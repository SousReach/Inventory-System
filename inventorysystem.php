<?php

class InventorySystem {
    private $inventory = [];
    private $filename = 'inventory.json';
    
    public function __construct() {
        $this->loadInventory();
    }
    
    // Load inventory from file
    private function loadInventory() {
        if (file_exists($this->filename)) {
            $data = file_get_contents($this->filename);
            $this->inventory = json_decode($data, true) ?? [];
            echo "Inventory loaded from {$this->filename}\n";
        } else {
            echo "No existing inventory file found. Starting fresh.\n";
        }
    }
    
    // Save inventory to file
    private function saveInventory() {
        $data = json_encode($this->inventory, JSON_PRETTY_PRINT);
        if (file_put_contents($this->filename, $data)) {
            echo "Inventory saved to {$this->filename}\n";
            return true;
        } else {
            echo "Error: Could not save inventory to file.\n";
            return false;
        }
    }
    
    // Display main menu
    private function displayMenu() {
        echo "\n" . str_repeat("=", 50) . "\n";
        echo "           INVENTORY MANAGEMENT SYSTEM\n";
        echo str_repeat("=", 50) . "\n";
        echo "1. Add Product\n";
        echo "2. Update Product\n";
        echo "3. Remove Product\n";
        echo "4. Search Product\n";
        echo "5. List All Products\n";
        echo "6. Save Inventory\n";
        echo "7. Exit\n";
        echo str_repeat("-", 50) . "\n";
        echo "Choose an option (1-7): ";
    }
    
    // Get user input with validation
    private function getInput($prompt, $type = 'string') {
        echo $prompt;
        $input = trim(fgets(STDIN));
        
        if ($type === 'number' && !is_numeric($input)) {
            echo "Error: Please enter a valid number.\n";
            return $this->getInput($prompt, $type);
        }
        
        if ($type === 'price' && (!is_numeric($input) || $input < 0)) {
            echo "Error: Please enter a valid price (positive number).\n";
            return $this->getInput($prompt, $type);
        }
        
        if ($type === 'quantity' && (!is_numeric($input) || $input < 0 || $input != (int)$input)) {
            echo "Error: Please enter a valid quantity (positive integer).\n";
            return $this->getInput($prompt, $type);
        }
        
        return $input;
    }
    
    // Add a new product
    private function addProduct() {
        echo "\n--- ADD NEW PRODUCT ---\n";
        
        $id = $this->getInput("Enter product ID: ");
        if (empty($id)) {
            echo "Error: Product ID cannot be empty.\n";
            return;
        }
        
        if (isset($this->inventory[$id])) {
            echo "Error: Product with ID '$id' already exists.\n";
            $update = $this->getInput("Do you want to update it instead? (y/n): ");
            if (strtolower($update) === 'y') {
                $this->updateProduct($id);
            }
            return;
        }
        
        $name = $this->getInput("Enter product name: ");
        if (empty($name)) {
            echo "Error: Product name cannot be empty.\n";
            return;
        }
        
        $quantity = (int)$this->getInput("Enter quantity: ", 'quantity');
        $price = (float)$this->getInput("Enter price: $", 'price');
        $category = $this->getInput("Enter category (optional): ");
        
        $this->inventory[$id] = [
            'name' => $name,
            'quantity' => $quantity,
            'price' => $price,
            'category' => $category ?: 'General',
            'added_date' => date('Y-m-d H:i:s')
        ];
        
        echo "Product '$name' added successfully!\n";
    }
    
    // Update existing product
    private function updateProduct($productId = null) {
        echo "\n--- UPDATE PRODUCT ---\n";
        
        if ($productId === null) {
            $productId = $this->getInput("Enter product ID to update: ");
        }
        
        if (!isset($this->inventory[$productId])) {
            echo "Error: Product with ID '$productId' not found.\n";
            return;
        }
        
        $product = $this->inventory[$productId];
        echo "Current product details:\n";
        $this->displayProduct($productId, $product);
        
        echo "\nWhat would you like to update?\n";
        echo "1. Name\n";
        echo "2. Quantity\n";
        echo "3. Price\n";
        echo "4. Category\n";
        echo "5. All fields\n";
        
        $choice = $this->getInput("Choose option (1-5): ", 'number');
        
        switch ($choice) {
            case 1:
                $newName = $this->getInput("Enter new name (current: {$product['name']}): ");
                if (!empty($newName)) {
                    $this->inventory[$productId]['name'] = $newName;
                }
                break;
            case 2:
                $newQuantity = (int)$this->getInput("Enter new quantity (current: {$product['quantity']}): ", 'quantity');
                $this->inventory[$productId]['quantity'] = $newQuantity;
                break;
            case 3:
                $newPrice = (float)$this->getInput("Enter new price (current: $" . number_format($product['price'], 2) . "): $", 'price');
                $this->inventory[$productId]['price'] = $newPrice;
                break;
            case 4:
                $newCategory = $this->getInput("Enter new category (current: {$product['category']}): ");
                if (!empty($newCategory)) {
                    $this->inventory[$productId]['category'] = $newCategory;
                }
                break;
            case 5:
                $newName = $this->getInput("Enter new name (current: {$product['name']}): ");
                $newQuantity = (int)$this->getInput("Enter new quantity (current: {$product['quantity']}): ", 'quantity');
                $newPrice = (float)$this->getInput("Enter new price (current: $" . number_format($product['price'], 2) . "): $", 'price');
                $newCategory = $this->getInput("Enter new category (current: {$product['category']}): ");
                
                if (!empty($newName)) $this->inventory[$productId]['name'] = $newName;
                $this->inventory[$productId]['quantity'] = $newQuantity;
                $this->inventory[$productId]['price'] = $newPrice;
                if (!empty($newCategory)) $this->inventory[$productId]['category'] = $newCategory;
                break;
            default:
                echo "Invalid option.\n";
                return;
        }
        
        $this->inventory[$productId]['updated_date'] = date('Y-m-d H:i:s');
        echo "Product updated successfully!\n";
    }
    
    // Remove a product
    private function removeProduct() {
        echo "\n--- REMOVE PRODUCT ---\n";
        
        $productId = $this->getInput("Enter product ID to remove: ");
        
        if (!isset($this->inventory[$productId])) {
            echo "Error: Product with ID '$productId' not found.\n";
            return;
        }
        
        $product = $this->inventory[$productId];
        echo "Product to be removed:\n";
        $this->displayProduct($productId, $product);
        
        $confirm = $this->getInput("\nAre you sure you want to remove this product? (y/n): ");
        
        if (strtolower($confirm) === 'y') {
            unset($this->inventory[$productId]);
            echo "Product removed successfully!\n";
        } else {
            echo "Product removal cancelled.\n";
        }
    }
    
    // Search for products
    private function searchProduct() {
        echo "\n--- SEARCH PRODUCTS ---\n";
        
        $searchTerm = $this->getInput("Enter search term (ID, name, or category): ");
        $results = [];
        
        foreach ($this->inventory as $id => $product) {
            if (stripos($id, $searchTerm) !== false ||
                stripos($product['name'], $searchTerm) !== false ||
                stripos($product['category'], $searchTerm) !== false) {
                $results[$id] = $product;
            }
        }
        
        if (empty($results)) {
            echo "No products found matching '$searchTerm'.\n";
        } else {
            echo "Found " . count($results) . " product(s):\n";
            $this->displayProducts($results);
        }
    }
    
    // List all products
    private function listAllProducts() {
        echo "\n--- ALL PRODUCTS ---\n";
        
        if (empty($this->inventory)) {
            echo "No products in inventory.\n";
            return;
        }
        
        echo "Sort by:\n";
        echo "1. ID\n";
        echo "2. Name\n";
        echo "3. Price\n";
        echo "4. Quantity\n";
        echo "5. Category\n";
        
        $sortChoice = $this->getInput("Choose sort option (1-5): ", 'number');
        $sortedInventory = $this->inventory;
        
        switch ($sortChoice) {
            case 1:
                ksort($sortedInventory);
                break;
            case 2:
                uasort($sortedInventory, function($a, $b) {
                    return strcasecmp($a['name'], $b['name']);
                });
                break;
            case 3:
                uasort($sortedInventory, function($a, $b) {
                    return $a['price'] <=> $b['price'];
                });
                break;
            case 4:
                uasort($sortedInventory, function($a, $b) {
                    return $b['quantity'] <=> $a['quantity'];
                });
                break;
            case 5:
                uasort($sortedInventory, function($a, $b) {
                    return strcasecmp($a['category'], $b['category']);
                });
                break;
            default:
                echo "Invalid option. Showing unsorted list.\n";
        }
        
        $this->displayProducts($sortedInventory);
        $this->displaySummary();
    }
    
    // Display a single product
    private function displayProduct($id, $product) {
        echo str_repeat("-", 40) . "\n";
        echo "ID: $id\n";
        echo "Name: {$product['name']}\n";
        echo "Quantity: {$product['quantity']}\n";
        echo "Price: $" . number_format($product['price'], 2) . "\n";
        echo "Category: {$product['category']}\n";
        echo "Total Value: $" . number_format($product['quantity'] * $product['price'], 2) . "\n";
        if (isset($product['added_date'])) {
            echo "Added: {$product['added_date']}\n";
        }
        if (isset($product['updated_date'])) {
            echo "Updated: {$product['updated_date']}\n";
        }
    }
    
    // Display multiple products
    private function displayProducts($products) {
        if (empty($products)) {
            echo "No products to display.\n";
            return;
        }
        
        // Header
        printf("%-10s %-20s %-8s %-10s %-15s %-12s\n", 
               "ID", "Name", "Qty", "Price", "Category", "Total Value");
        echo str_repeat("-", 80) . "\n";
        
        // Products
        foreach ($products as $id => $product) {
            $totalValue = $product['quantity'] * $product['price'];
            printf("%-10s %-20s %-8d $%-9.2f %-15s $%-11.2f\n",
                   substr($id, 0, 10),
                   substr($product['name'], 0, 20),
                   $product['quantity'],
                   $product['price'],
                   substr($product['category'], 0, 15),
                   $totalValue);
        }
        echo str_repeat("-", 80) . "\n";
    }
    
    // Display inventory summary
    private function displaySummary() {
        $totalProducts = count($this->inventory);
        $totalQuantity = array_sum(array_column($this->inventory, 'quantity'));
        $totalValue = 0;
        $categories = [];
        
        foreach ($this->inventory as $product) {
            $totalValue += $product['quantity'] * $product['price'];
            $categories[$product['category']] = ($categories[$product['category']] ?? 0) + 1;
        }
        
        echo "\nINVENTORY SUMMARY:\n";
        echo "Total Products: $totalProducts\n";
        echo "Total Quantity: $totalQuantity\n";
        echo "Total Value: $" . number_format($totalValue, 2) . "\n";
        echo "Categories: " . implode(', ', array_keys($categories)) . "\n";
    }
    
    // Main application loop
    public function run() {
        echo "Welcome to the Inventory Management System!\n";
        
        while (true) {
            $this->displayMenu();
            $choice = $this->getInput("", 'number');
            
            switch ($choice) {
                case 1:
                    $this->addProduct();
                    break;
                case 2:
                    $this->updateProduct();
                    break;
                case 3:
                    $this->removeProduct();
                    break;
                case 4:
                    $this->searchProduct();
                    break;
                case 5:
                    $this->listAllProducts();
                    break;
                case 6:
                    $this->saveInventory();
                    break;
                case 7:
                    echo "\nSaving inventory before exit...\n";
                    $this->saveInventory();
                    echo "Thank you for using the Inventory Management System!\n";
                    exit(0);
                default:
                    echo "Invalid option. Please choose 1-7.\n";
            }
            
            // Pause before showing menu again
            echo "\nPress Enter to continue...";
            fgets(STDIN);
        }
    }
}

// Initialize and run the inventory system
$inventory = new InventorySystem();
$inventory->run();

?>