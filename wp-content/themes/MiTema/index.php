<?php
// Cargar el header del tema
get_header();
?>

<div class="container">
    <!-- Contenido principal -->
    <main class="site-main">
        <?php
        // Verificar si hay contenido para mostrar
        if (have_posts()) :
            while (have_posts()) : the_post();
                ?>
                <!-- Mostrar el título y contenido de la entrada o página -->
                <h1><?php the_title(); ?></h1>
                <div class="content">
                    <?php the_content(); ?>
                </div>
                <?php
            endwhile;
        else :
            ?>
            <p>No se encontró contenido.</p>
            <?php
        endif;
        ?>
    </main>
</div>

<?php
// Cargar el footer del tema
get_footer();
?>