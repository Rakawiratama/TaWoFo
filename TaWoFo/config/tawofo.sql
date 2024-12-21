-- Active: 1731291578031@@127.0.0.1@3306@phpmyadmin
CREATE DATABASE taWoFo;

USE taWoFo;

-- Tabel untuk menyimpan data karyawan
CREATE TABLE employees (
    id INT AUTO_INCREMENT PRIMARY KEY, -- ID unik untuk setiap karyawan
    name VARCHAR(100) NOT NULL,        -- Nama karyawan
    email VARCHAR(100) NOT NULL,       -- Email karyawan
    phone VARCHAR(15) NOT NULL,        -- Nomor telepon karyawan
    position VARCHAR(50) NOT NULL,     -- Posisi pekerjaan
    salary DECIMAL(10, 2) NOT NULL     -- Gaji karyawan
);


-- Tabel untuk menyimpan data gaji
CREATE TABLE payroll (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    salary DECIMAL(10, 2) NOT NULL,
    month INT NOT NULL,
    year INT NOT NULL,
    FOREIGN KEY (employee_id) REFERENCES employees(id)
);

-- Tabel untuk menyimpan laporan
CREATE TABLE reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    report_date DATE NOT NULL,
    report_content TEXT NOT NULL
);

-- Tabel untuk menyimpan pengaturan aplikasi
CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_name VARCHAR(100) NOT NULL,
    setting_value VARCHAR(255) NOT NULL
);

-- Tabel untuk menyimpan data user login
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

