<?php
// Enhanced Items API for Godspeed Inventory Management System

require_once 'config.php';
session_start();

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// Check authentication
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized', 'message' => 'Please login to continue']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    handleGetItems();
} 
else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (isset($data['action'])) {
        switch ($data['action']) {
            case 'add':
                handleAddItem($data);
                break;
                
            case 'update':
                handleUpdateItem($data);
                break;
                
            case 'delete':
                handleDeleteItem($data);
                break;
                
            case 'search':
                handleSearchItems($data);
                break;
                
            default:
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
                break;
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'No action specified']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}

function handleGetItems() {
    global $pdo;
    
    try {
        $stmt = $pdo->query("SELECT * FROM items WHERE is_active = 1 ORDER BY name");
        $items = $stmt->fetchAll();
        
        echo json_encode($items);
    } catch (Exception $e) {
        error_log("Failed to fetch items: " . $e->getMessage());
        echo json_encode(['error' => 'Failed to fetch items', 'message' => 'Database error occurred']);
    }
}

function handleAddItem($data) {
    global $pdo;
    
    try {
        // Validate required fields
        $name = sanitizeInput($data['name'] ?? '');
        $quantity = intval($data['quantity'] ?? 0);
        
        if (empty($name)) {
            echo json_encode(['success' => false, 'message' => 'Item name is required']);
            return;
        }
        
        if ($quantity < 0) {
            echo json_encode(['success' => false, 'message' => 'Quantity cannot be negative']);
            return;
        }
        
        // Check if item already exists
        $stmt = $pdo->prepare("SELECT id FROM items WHERE name = ? AND is_active = 1");
        $stmt->execute([$name]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Item with this name already exists']);
            return;
        }
        
        // Prepare item data
        $itemData = [
            'name' => $name,
            'description' => sanitizeInput($data['description'] ?? ''),
            'quantity' => $quantity,
            'min_quantity' => intval($data['min_quantity'] ?? 0),
            'max_quantity' => !empty($data['max_quantity']) ? intval($data['max_quantity']) : null,
            'category' => sanitizeInput($data['category'] ?? ''),
            'unit' => sanitizeInput($data['unit'] ?? 'pcs'),
            'price' => !empty($data['price']) ? floatval($data['price']) : null,
            'supplier' => sanitizeInput($data['supplier'] ?? ''),
            'location' => sanitizeInput($data['location'] ?? '')
        ];
        
        // Insert item
        $stmt = $pdo->prepare("
            INSERT INTO items (name, description, quantity, min_quantity, max_quantity, category, unit, price, supplier, location) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $itemData['name'],
            $itemData['description'],
            $itemData['quantity'],
            $itemData['min_quantity'],
            $itemData['max_quantity'],
            $itemData['category'],
            $itemData['unit'],
            $itemData['price'],
            $itemData['supplier'],
            $itemData['location']
        ]);
        
        $itemId = $pdo->lastInsertId();
        
        // Log the action
        logAuditTrail($_SESSION['user_id'], 'CREATE', 'items', $itemId, null, $itemData);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Item added successfully',
            'id' => $itemId
        ]);
        
    } catch (Exception $e) {
        error_log("Failed to add item: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to add item. Please try again.']);
    }
}

function handleUpdateItem($data) {
    global $pdo;
    
    try {
        // Validate required fields
        $id = intval($data['id'] ?? 0);
        $name = sanitizeInput($data['name'] ?? '');
        $quantity = intval($data['quantity'] ?? 0);
        
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid item ID']);
            return;
        }
        
        if (empty($name)) {
            echo json_encode(['success' => false, 'message' => 'Item name is required']);
            return;
        }
        
        if ($quantity < 0) {
            echo json_encode(['success' => false, 'message' => 'Quantity cannot be negative']);
            return;
        }
        
        // Get current item data for audit log
        $stmt = $pdo->prepare("SELECT * FROM items WHERE id = ? AND is_active = 1");
        $stmt->execute([$id]);
        $currentItem = $stmt->fetch();
        
        if (!$currentItem) {
            echo json_encode(['success' => false, 'message' => 'Item not found']);
            return;
        }
        
        // Check if name already exists for different item
        $stmt = $pdo->prepare("SELECT id FROM items WHERE name = ? AND id != ? AND is_active = 1");
        $stmt->execute([$name, $id]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Item with this name already exists']);
            return;
        }
        
        // Prepare updated item data
        $itemData = [
            'name' => $name,
            'description' => sanitizeInput($data['description'] ?? $currentItem['description']),
            'quantity' => $quantity,
            'min_quantity' => intval($data['min_quantity'] ?? $currentItem['min_quantity']),
            'max_quantity' => !empty($data['max_quantity']) ? intval($data['max_quantity']) : $currentItem['max_quantity'],
            'category' => sanitizeInput($data['category'] ?? $currentItem['category']),
            'unit' => sanitizeInput($data['unit'] ?? $currentItem['unit']),
            'price' => !empty($data['price']) ? floatval($data['price']) : $currentItem['price'],
            'supplier' => sanitizeInput($data['supplier'] ?? $currentItem['supplier']),
            'location' => sanitizeInput($data['location'] ?? $currentItem['location'])
        ];
        
        // Update item
        $stmt = $pdo->prepare("
            UPDATE items SET 
                name = ?, description = ?, quantity = ?, min_quantity = ?, max_quantity = ?, 
                category = ?, unit = ?, price = ?, supplier = ?, location = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $itemData['name'],
            $itemData['description'],
            $itemData['quantity'],
            $itemData['min_quantity'],
            $itemData['max_quantity'],
            $itemData['category'],
            $itemData['unit'],
            $itemData['price'],
            $itemData['supplier'],
            $itemData['location'],
            $id
        ]);
        
        // Log the action
        logAuditTrail($_SESSION['user_id'], 'UPDATE', 'items', $id, $currentItem, $itemData);
        
        echo json_encode(['success' => true, 'message' => 'Item updated successfully']);
        
    } catch (Exception $e) {
        error_log("Failed to update item: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to update item. Please try again.']);
    }
}

function handleDeleteItem($data) {
    global $pdo;
    
    try {
        $id = intval($data['id'] ?? 0);
        
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid item ID']);
            return;
        }
        
        // Get current item data for audit log
        $stmt = $pdo->prepare("SELECT * FROM items WHERE id = ? AND is_active = 1");
        $stmt->execute([$id]);
        $currentItem = $stmt->fetch();
        
        if (!$currentItem) {
            echo json_encode(['success' => false, 'message' => 'Item not found']);
            return;
        }
        
        // Soft delete (mark as inactive)
        $stmt = $pdo->prepare("UPDATE items SET is_active = 0 WHERE id = ?");
        $stmt->execute([$id]);
        
        // Log the action
        logAuditTrail($_SESSION['user_id'], 'DELETE', 'items', $id, $currentItem, null);
        
        echo json_encode(['success' => true, 'message' => 'Item deleted successfully']);
        
    } catch (Exception $e) {
        error_log("Failed to delete item: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to delete item. Please try again.']);
    }
}

function handleSearchItems($data) {
    global $pdo;
    
    try {
        $searchTerm = sanitizeInput($data['search'] ?? '');
        $category = sanitizeInput($data['category'] ?? '');
        $status = sanitizeInput($data['status'] ?? '');
        
        $whereConditions = ['is_active = 1'];
        $params = [];
        
        if (!empty($searchTerm)) {
            $whereConditions[] = "(name LIKE ? OR description LIKE ? OR supplier LIKE ?)";
            $searchParam = "%$searchTerm%";
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
        }
        
        if (!empty($category)) {
            $whereConditions[] = "category = ?";
            $params[] = $category;
        }
        
        if (!empty($status)) {
            switch ($status) {
                case 'low_stock':
                    $whereConditions[] = "quantity > 0 AND quantity < min_quantity";
                    break;
                case 'out_of_stock':
                    $whereConditions[] = "quantity = 0";
                    break;
                case 'in_stock':
                    $whereConditions[] = "quantity >= min_quantity";
                    break;
            }
        }
        
        $whereClause = implode(' AND ', $whereConditions);
        $sql = "SELECT * FROM items WHERE $whereClause ORDER BY name";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $items = $stmt->fetchAll();
        
        echo json_encode($items);
        
    } catch (Exception $e) {
        error_log("Failed to search items: " . $e->getMessage());
        echo json_encode(['error' => 'Failed to search items', 'message' => 'Database error occurred']);
    }
}
?> 