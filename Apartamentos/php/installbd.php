<?php
$host = 'localhost';
$db = 'Apartamentos';
$rootuser = 'root';
$rootpass = 'root';
$adminuser = 'admin';
$adminpass = 'admin';


// CREO UN ARRAY PARA LOS MENSAJES
$messages=[
    "Se ha conectado correctamente con root.",
    "Se ha creado correctamente la base de datos.",
    "Se ha creado correctamente el usuario Administrador.",
    "Se le han asignado correctamente los permisos.",
    "Se ha desconectado correctamente de Root.",
    "Se ha conectado correctamente con el usuario Administrador.",
    "Se han creado las tablas y se han insertado los datos.",
    "Se ha desconectado del usuario Administrador.",
    "¡Se creó con éxito la base de datos Apartamentos!"
];

try {
// CONECTO CON ROOT
  $pdoRoot = new PDO("mysql:host=$host", $rootuser, $rootpass);
  $pdoRoot->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  echo $messages[0] . "<br>";
// CREO LA BASE DE DATOS CON ROOT
$consultaroot= [
    "DROP DATABASE IF EXISTS $db",
    "CREATE DATABASE IF NOT EXISTS $db",
    "CREATE USER IF NOT EXISTS '$adminuser'@'$host' IDENTIFIED BY '$adminpass'",
    "GRANT ALL PRIVILEGES ON $db.* TO '$adminuser'@'$host'",
    "SET GLOBAL log_bin_trust_function_creators = 1;"
]; 

foreach($consultaroot as $consultatr) {
    $pdoRoot->exec($consultatr);
}

foreach ([$messages[1], $messages[2], $messages[3]] as $mensaje) {
    echo $mensaje. "<br>";
}

// DESCONECTO DE ROOT
$pdoRoot = null;

foreach ([$messages[4], $messages[5]] as $mensaje) {
    echo $mensaje. "<br>";
}

// CONECTO CON ADMIN
$pdoAdmin = new PDO("mysql:host=$host", $adminuser, $adminpass);
$pdoAdmin->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdoAdmin->exec('use Apartamentos;');

//CREO LAS TABLAS
$consultadmin= [
    "CREATE TABLE IF NOT EXISTS Usuarios (
        id_usuario INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL,
        correo_electronico VARCHAR(100) UNIQUE NOT NULL,
        contrasena VARCHAR(255) NOT NULL,
        telefono VARCHAR(15),
        administrador BOOLEAN DEFAULT FALSE,
        fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );",
    "CREATE TABLE IF NOT EXISTS CodigosDescuento (
        id_codigo INT AUTO_INCREMENT PRIMARY KEY,
        codigo VARCHAR(50) UNIQUE NOT NULL,
        descripcion VARCHAR(255),
        descuento_porcentaje DECIMAL(5, 2) NOT NULL,
        fecha_expiracion DATE NOT NULL
    );",
    "CREATE TABLE IF NOT EXISTS Apartamentos (
        id_apartamento INT AUTO_INCREMENT PRIMARY KEY,
        titulo VARCHAR(150) NOT NULL,
        descripcion TEXT NOT NULL,
        direccion VARCHAR(255) NOT NULL,
        ciudad VARCHAR(100) NOT NULL,
        pais VARCHAR(100) NOT NULL,
        precio DECIMAL(10, 2) NOT NULL,
        disponibilidad BOOLEAN DEFAULT TRUE,
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );",
    "CREATE TABLE IF NOT EXISTS FotosApartamentos (
        id_foto INT AUTO_INCREMENT PRIMARY KEY,
        id_apartamento INT NOT NULL,
        url_foto VARCHAR(255) NOT NULL,
        descripcion VARCHAR(255),
        portada TINYINT(1) DEFAULT 0,
        FOREIGN KEY (id_apartamento) REFERENCES Apartamentos(id_apartamento) ON DELETE CASCADE
    );",
    "CREATE TABLE IF NOT EXISTS Reservas (
        id_reserva INT AUTO_INCREMENT PRIMARY KEY,
        id_usuario INT NOT NULL,
        id_apartamento INT NOT NULL,
        fecha_reserva DATE NOT NULL,
        fecha_inicio DATE NOT NULL,
        fecha_fin DATE NOT NULL,
        estado VARCHAR(50) DEFAULT 'pendiente',
        FOREIGN KEY (id_usuario) REFERENCES Usuarios(id_usuario) ON DELETE CASCADE,
        FOREIGN KEY (id_apartamento) REFERENCES Apartamentos(id_apartamento) ON DELETE CASCADE
    );",
    "CREATE TABLE IF NOT EXISTS Pagos (
        id_pago INT AUTO_INCREMENT PRIMARY KEY,
        id_reserva INT NOT NULL,
        metodo_pago VARCHAR(50) NOT NULL,
        monto_pagado DECIMAL(10, 2) NOT NULL,
        fecha_pago TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_reserva) REFERENCES Reservas(id_reserva) ON DELETE CASCADE
    );",
        "CREATE TABLE IF NOT EXISTS CuponesAplicados (
        id_aplicacion INT AUTO_INCREMENT PRIMARY KEY,
        id_usuario INT NOT NULL,
        id_codigo INT NOT NULL,
        fecha_aplicacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_usuario) REFERENCES Usuarios(id_usuario) ON DELETE CASCADE,
        FOREIGN KEY (id_codigo) REFERENCES CodigosDescuento(id_codigo) ON DELETE CASCADE
    );",     
        "CREATE TABLE IF NOT EXISTS VisitasApartamentos (
        id_visita INT AUTO_INCREMENT PRIMARY KEY,
        id_apartamento INT NOT NULL,
        fecha_visita TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_apartamento) REFERENCES Apartamentos(id_apartamento) ON DELETE CASCADE
    );",
        "CREATE TABLE IF NOT EXISTS Auditoria (
    id_auditoria INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) DEFAULT 'Desconocido',
    tabla VARCHAR(50) NOT NULL,
    accion ENUM('INSERT', 'UPDATE', 'DELETE') NOT NULL,
    datos_anteriores TEXT,
    datos_nuevos TEXT,
    fecha_accion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );"   
];

foreach($consultadmin as $consultadm) {
    $pdoAdmin->exec($consultadm);
}

// INSERTO LOS DATOS EN LA TABLA LIBROS
$consultadmin = [ 
    "INSERT IGNORE INTO Usuarios (nombre, correo_electronico, contrasena, telefono, administrador) 
    VALUES 
    ('Robinson Marcano', 'Robin@gmail.com', '1234', '1234567890', FALSE),
    ('Jorge', 'Jorge@gmail.com', '1234', '111222333', TRUE);",

    "INSERT IGNORE INTO CodigosDescuento (codigo, descripcion, descuento_porcentaje, fecha_expiracion) 
    VALUES 
    ('DESC10', 'Descuento del 10% en la primera reserva', 10.00, '2025-12-31'),
    ('VERANO20', 'Descuento del 20% para el verano', 20.00, '2024-06-30');",
    
    "INSERT IGNORE INTO Apartamentos (titulo, descripcion, direccion, ciudad, pais, precio, disponibilidad) 
    VALUES 
    ('Apartamento de lujo con vista al mar', 'Hermoso apartamento con vista directa al mar, 2 habitaciones, 1 baño, cocina completa.', 'Calle Mar 123', 'Tazacorte', 'España', 90.00, TRUE),
    ('Apartamento céntrico en Los Llanos de Aridane', 'Ubicación inmejorable en el corazón de Los Llanos de Aridane, ideal para turistas.', 'Calle Sol 45', 'Los Llanos de Aridane', 'España', 100.00, TRUE),
    ('Apartamento moderno en Santa Cruz de Tenerife', 'Disfruta de la modernidad con este hermoso apartamento completamente equipado.', 'Avenida Principal 100', 'Santa Cruz de Tenerife', 'España', 95.00, TRUE),
    ('Estudio acogedor en San Pedro', 'Estudio ideal para viajeros solitarios o parejas, cómodo y funcional.', 'Calle Sevilla 200', 'Breña Alta', 'España', 90.00, TRUE),
    ('Cómodo apartamento en El Paso', 'Amplio piso con terraza privada y vistas panorámicas de la ciudad.', 'Plaza Central 12', 'El Paso', 'España', 80.00, TRUE);",
    
    "INSERT IGNORE INTO FotosApartamentos (id_apartamento, url_foto, descripcion) 
    VALUES 
    (1, '1a.png', 'Vista principal del apartamento'),
    (1, '1b.png', 'Vista de la cocina'),
    (1, '1c.png', 'Vista del dormitorio principal'),
    (2, '2a.png', 'Vista frontal del apartamento'),
    (2, '2b.png', 'Vista del dormitorio principal'),
    (2, '2c.png', 'Vista del baño'),
    (3, '3a.png', 'Vista general del apartamento'),
    (3, '3b.png', 'Vista de la cocina'),
    (3, '3c.png', 'Vista de la habitación principal'),
    (4, '4a.png', 'Vista del salón principal'),
    (4, '4b.png', 'Vista de la cocina integrada'),
    (4, '4c.png', 'Vista del dormitorio principal'),
    (5, '5a.png', 'Vista de la terraza privada'),
    (5, '5b.png', 'Vista de la cocina'),
    (5, '5c.png', 'Vista de la habitación principal');"
];

foreach($consultadmin as $consultadm) {
    $pdoAdmin->exec($consultadm);
}

foreach ([$messages[6], $messages[7], $messages[8]] as $mensaje) {
    echo $mensaje. "<br>";
}
// INSTALACION DE TRIGGERS

$consultadmin=[
    "CREATE TRIGGER auditoria_apartamentos_insert
    AFTER INSERT ON Apartamentos
    FOR EACH ROW
    BEGIN
        INSERT INTO Auditoria (usuario, tabla, accion, datos_nuevos)
        VALUES (USER(), 'Apartamentos', 'INSERT', CONCAT('id_apartamento=', NEW.id_apartamento, ', titulo=', NEW.titulo, ', direccion=', NEW.direccion, ', precio=', NEW.precio));
    END;",
    
    "CREATE TRIGGER auditoria_apartamentos_update
    AFTER UPDATE ON Apartamentos
    FOR EACH ROW
    BEGIN
        INSERT INTO Auditoria (usuario, tabla, accion, datos_anteriores, datos_nuevos)
        VALUES (USER(), 'Apartamentos', 'UPDATE', 
                CONCAT('id_apartamento=', OLD.id_apartamento, ', titulo=', OLD.titulo, ', direccion=', OLD.direccion, ', precio=', OLD.precio), 
                CONCAT('id_apartamento=', NEW.id_apartamento, ', titulo=', NEW.titulo, ', direccion=', NEW.direccion, ', precio=', NEW.precio));
    END;",
    
    "CREATE TRIGGER auditoria_apartamentos_delete
    AFTER DELETE ON Apartamentos
    FOR EACH ROW
    BEGIN
        INSERT INTO Auditoria (usuario, tabla, accion, datos_anteriores)
        VALUES (USER(), 'Apartamentos', 'DELETE', CONCAT('id_apartamento=', OLD.id_apartamento, ', titulo=', OLD.titulo, ', direccion=', OLD.direccion, ', precio=', OLD.precio));
    END;",
    
    "CREATE TRIGGER auditoria_fotosapartamentos_insert
    AFTER INSERT ON FotosApartamentos
    FOR EACH ROW
    BEGIN
        INSERT INTO Auditoria (usuario, tabla, accion, datos_nuevos)
        VALUES (USER(), 'FotosApartamentos', 'INSERT', CONCAT('id_foto=', NEW.id_foto, ', id_apartamento=', NEW.id_apartamento, ', url_foto=', NEW.url_foto));
    END;",
    
    "CREATE TRIGGER auditoria_fotosapartamentos_update
    AFTER UPDATE ON FotosApartamentos
    FOR EACH ROW
    BEGIN
        INSERT INTO Auditoria (usuario, tabla, accion, datos_anteriores, datos_nuevos)
        VALUES (USER(), 'FotosApartamentos', 'UPDATE', 
                CONCAT('id_foto=', OLD.id_foto, ', url_foto=', OLD.url_foto), 
                CONCAT('id_foto=', NEW.id_foto, ', url_foto=', NEW.url_foto));
    END;",
    
    "CREATE TRIGGER auditoria_fotosapartamentos_delete
    AFTER DELETE ON FotosApartamentos
    FOR EACH ROW
    BEGIN
        INSERT INTO Auditoria (usuario, tabla, accion, datos_anteriores)
        VALUES (USER(), 'FotosApartamentos', 'DELETE', CONCAT('id_foto=', OLD.id_foto, ', url_foto=', OLD.url_foto));
    END;"
];

foreach($consultadmin as $consultadm) {
    $pdoAdmin->exec($consultadm);
}

try {
    $stmt = $pdoAdmin->query('SELECT id_usuario, contrasena FROM Usuarios');
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($usuarios as $usuario) {
        $hashed_password = password_hash($usuario['contrasena'], PASSWORD_DEFAULT);
        $update_stmt = $pdoAdmin->prepare('UPDATE Usuarios SET contrasena = ? WHERE id_usuario = ?');
        $update_stmt->execute([$hashed_password, $usuario['id_usuario']]);
    }
    
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}

echo "Se han cifrado las credenciales correctamente.";

$consultita = $pdoAdmin->query("SELECT * FROM USUARIOS WHERE ADMINISTRADOR=TRUE;")->fetchAll(PDO::FETCH_ASSOC);
echo "<p>Recuerda que estos son tus datos para acceder como Administrador:</p>";
echo "<ul>";
foreach($consultita as $raw){
    echo "<li>Tu nombre: ".$raw['nombre']."</li>";
    echo "<li>Tu correo de acceso: ".$raw['correo_electronico']."</li>";
    echo "<li>Tu contraseña: 1234</li>";
}
echo "</ul>";

echo "<a href='../admin/index.php'>Ir al panel de control</a>";
// DESCONECTO DE ADMIN
$pdoAdmin = null;
} catch (PDOException $e) {
    echo "Error:    ".$e->getMessage()."";
}
?>