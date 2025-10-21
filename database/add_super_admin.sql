-- SQL Script to add a new Super Admin user
-- IMPORTANT: Replace '[HASHED_PASSWORD_HERE]' with a securely hashed password.
-- You can generate a hashed password using PHP's password_hash() function.
-- Example:
-- <?php
-- $password = 'mX7#Aq!D9v^H5tPz@w3*LuG2s$RkJ8yBn%fC1eQxZo6T!MhKjVr4pW0Nd^Ub';
-- $hashed_password = password_hash($password, PASSWORD_DEFAULT);
-- echo $hashed_password;
-- ?>
-- Copy the output from running the above PHP script and paste it into the SQL query below.

INSERT INTO usuarios (nombre, email, contrasena)
VALUES ('Super Admin', 'etebachalegroup@gmail.com', '[HASHED_PASSWORD_HERE]');
