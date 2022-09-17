<?php include_once __DIR__ . '/header-dashboard.php' ?>

<?php if (count($proyectos) === 0) : ?>
    <p class="no-proyecto">
        No hay proyectos Aun!
        <a href="/crear-proyectos">Comienza creando Uno</a>
    </p>
<?php else : ?>

    <ul class="listado-proyecto">
        <?php foreach ($proyectos as $proyecto) :  ?>
            <li class="proyecto">
                <a href="<?php echo "/proyecto?url=" . $proyecto->url ?>">
                    <?php echo $proyecto->proyecto; ?>
                </a>
            </li>
        <?php endforeach ?>
    </ul>

<?php endif ?>


<?php include_once __DIR__ . '/footer-dashboard.php' ?>