<?php
class AdminPanel {
    private $db;
    
    public function __construct() {
        $this->db = new PDO("mysql:host=localhost;dbname=dijimarket", "username", "password");
    }
    
    // Ürün Yönetimi
    public function addProduct($data) {
        $sql = "INSERT INTO products (name, description, price, category_id, stock, image) 
                VALUES (:name, :description, :price, :category_id, :stock, :image)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }
    
    public function updateProduct($id, $data) {
        $sql = "UPDATE products SET 
                name = :name,
                description = :description,
                price = :price,
                category_id = :category_id,
                stock = :stock,
                image = :image
                WHERE id = :id";
        $data['id'] = $id;
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }
    
    public function deleteProduct($id) {
        $sql = "DELETE FROM products WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
    
    // Sipariş Yönetimi
    public function getOrders($status = null) {
        $sql = "SELECT * FROM orders";
        if ($status) {
            $sql .= " WHERE status = :status";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['status' => $status]);
        } else {
            $stmt = $this->db->query($sql);
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function updateOrderStatus($orderId, $status) {
        $sql = "UPDATE orders SET status = :status WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $orderId,
            'status' => $status
        ]);
    }
    
    // Kullanıcı Yönetimi
    public function getUsers() {
        $sql = "SELECT * FROM users";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function banUser($userId) {
        $sql = "UPDATE users SET status = 'banned' WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $userId]);
    }
    
    // İstatistikler
    public function getDashboardStats() {
        $stats = [];
        
        // Toplam Satış
        $sql = "SELECT SUM(total_amount) as total_sales FROM orders WHERE status = 'completed'";
        $stmt = $this->db->query($sql);
        $stats['total_sales'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_sales'];
        
        // Günlük Satış
        $sql = "SELECT COUNT(*) as daily_orders FROM orders 
                WHERE DATE(created_at) = CURDATE()";
        $stmt = $this->db->query($sql);
        $stats['daily_orders'] = $stmt->fetch(PDO::FETCH_ASSOC)['daily_orders'];
        
        // Aktif Kullanıcılar
        $sql = "SELECT COUNT(*) as active_users FROM users 
                WHERE last_login >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
        $stmt = $this->db->query($sql);
        $stats['active_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['active_users'];
        
        return $stats;
    }
}