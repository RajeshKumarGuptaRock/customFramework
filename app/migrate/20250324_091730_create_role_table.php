<?php

return new class {
    public function create()
    {
        // Add or Modify your table structure and query logic here
        return "CREATE TABLE role (
                    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    firstname VARCHAR(30) NOT NULL,
                    lastname VARCHAR(30) NOT NULL,
                    email VARCHAR(50),
                    reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )";
    
    }

    public function drop()
    {
       return "DROP TABLE role";
        // Add rollback logic here
    }
};