<div class="contenedor olvide">

    <?php include_once __DIR__ . '/../templates/nombre-sitio.php' ?>

    <div class="contenedor-sm">
        <p class="descripcion-pagina">Recuperar tu Aceeso en UpTask</p>

        <?php include_once __DIR__ . '/../templates/alertas.php' ?>
        
        <form action="/olvide" class="formulario" method="POST">
            <div class="campo">
                <label for="email">Email</label>
                <input type="email" id="email" placeholder="Tu Email" name="email">
            </div>

            <input type="submit" class="boton" value="Recuperar Password">
        </form>

        <div class="acciones">
            <a href="/">Iniciar Sesion</a>
            <a href="/crear">Obtener una cuenta</a>
        </div>
    </div>
    <!--Contenedor SM  -->

</div>