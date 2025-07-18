# ใช้ image PHP + Apache พื้นฐาน
FROM php:8.2-apache

# คัดลอกไฟล์ทั้งหมดไปยังโฟลเดอร์ /var/www/html ใน container
COPY . /var/www/html/

# เปิดพอร์ต 80
EXPOSE 80

# กำหนด working directory
WORKDIR /var/www/html

# ติดตั้ง mysqli extension สำหรับเชื่อมต่อ MySQL
RUN docker-php-ext-install mysqli
