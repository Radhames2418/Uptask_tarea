<?php

namespace Classes;

use PHPMailer\PHPMailer\PHPMailer;

class Email
{
    protected $email;
    protected $nombre;
    protected $token;

    public function __construct($email, $nombre, $token)
    {
        $this->email = $email;
        $this->nombre = $nombre;
        $this->token = $token;
    }

    public function enviarConfirmacion()
    {
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->Host = 'smtp.mailtrap.io';
        $mail->SMTPAuth = true;
        $mail->Port = 2525;
        $mail->Username = 'f30bcc3bcdff96';
        $mail->Password = '40fd5e65e5b7e3';

        $mail->setFrom('cuentas@uptask.com');
        $mail->addAddress('cuentas@uptask.com', 'uptask.com');
        $mail->Subject = 'Confirma tu cuenta';

        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';

        $contenido = '<html>';
        $contenido .= '<p><strong>Hola '. $this->nombre . '</strong> Has creado tu cuenta en UpTask, solo debes confirmala en el siguiente enlace</p>';
        $contenido .= '<p>Presiona aqui: <a href="http://localhost:5500/confirmar?token='. $this->token .'">Confirmar Cuenta</a></p>';
        $contenido .= '<p>Si tu no creaste esta cuenta, pues ignora este mensaje</p>';
        $contenido .= '</html>';
        
        $mail->Body = $contenido;
        
        // Enviar Email
        $mail->send();
    }

    public function enviarInstrucciones()
    {
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->Host = 'smtp.mailtrap.io';
        $mail->SMTPAuth = true;
        $mail->Port = 2525;
        $mail->Username = 'f30bcc3bcdff96';
        $mail->Password = '40fd5e65e5b7e3';

        $mail->setFrom('cuentas@uptask.com');
        $mail->addAddress('cuentas@uptask.com', 'uptask.com');
        $mail->Subject = 'Restablece tu password';

        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';

        $contenido = '<html>';
        $contenido .= '<p><strong>Hola '. $this->nombre . '</strong> Has olvidado tu password, sigue el siguiente enlace para recuperarlo</p>';
        $contenido .= '<p>Presiona aqui: <a href="http://localhost:5500/reestablecer?token='. $this->token .'">Reestablecer Cuenta</a></p>';
        $contenido .= '<p>Si tu no creaste esta cuenta, pues ignora este mensaje</p>';
        $contenido .= '</html>';
        
        $mail->Body = $contenido;
        
        // Enviar Email
        $mail->send();
    }
}
