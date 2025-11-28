<?php

return new class {
    public function create()
    {
        // Add or Modify your table structure and query logic here
        return "CREATE TABLE products (
                    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    productname VARCHAR(30) NOT NULL,
                    slug VARCHAR(30) NOT NULL,
                    price VARCHAR(50),
                    reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )";
    
    }

    public function drop()
    {
       return "DROP TABLE products";
        // Add rollback logic here
    }
};