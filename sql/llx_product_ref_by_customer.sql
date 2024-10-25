CREATE TABLE llx_product_ref_by_customer
(
    rowid INT AUTO_INCREMENT PRIMARY KEY,
    entity INT,
    datec DATETIME,
    fk_soc INT NOT NULL,
    fk_product INT NOT NULL,
    fk_user INT,
    import_key TEXT,
    ref_customer_prd TEXT NOT NULL,
    tms TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_product_ref_customer_prd_bycustomer_societe
        FOREIGN KEY (fk_soc) REFERENCES llx_societe (rowid) ON UPDATE CASCADE,
    CONSTRAINT fk_product_ref_customer_prd_bycustomer_product
        FOREIGN KEY (fk_product) REFERENCES llx_product (rowid) ON UPDATE CASCADE,
    CONSTRAINT fk_product_customer_ref_user
        FOREIGN KEY (fk_user) REFERENCES llx_user (rowid) ON UPDATE CASCADE
);