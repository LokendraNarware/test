<?php

if (!file_exists("includes/init.php")) {
    die("Not installed yet. Go to the <a href='install/'>install/</a> directory.");
}

require "includes/init.php";
$page->setTitle("Home")->setPage(1)->header();

?>

<section class="jumbo">
    <div class="container">
        <h1><?php pt("Get ahead of your competition"); ?></h1>
        <h2><?php pt("Powerful next-generation tools for search engine optimization at your fingertips."); ?></h2>

        <a href="tools.php"><?php pt("Get started"); ?></a>
    </div>
</section>

<section class="icons">
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <div class="icon"><i class="material-icons">favorite</i></div>
                <h3><?php pt("Simple"); ?></h3>
                <p><?php pt("Get the information you need quickly and neatly, with no hassle."); ?></p>
            </div>
            <div class="col-md-4">
                <div class="icon"><i class="material-icons">layers</i></div>
                <h3><?php pt("Organized"); ?></h3>
                <p><?php pt("Create an account and easily switch between multiple websites."); ?></p>
            </div>
            <div class="col-md-4">
                <div class="icon"><i class="material-icons">verified_user</i></div>
                <h3><?php pt("Safe"); ?></h3>
                <p><?php pt("Our tools are safe and don't harm your search engine ranks."); ?></p>
            </div>
        </div>
    </div>
</section>

<section class="home-tools">
    <div class="container">
        <form action="tools.php" method="get">
            <input type="text" name="site" placeholder="<?php pt("www.example.com"); ?>"> <input type="submit" value="<?php pt("Get started"); ?>">
        </form>
    </div>
</section>

<?php
$page->footer();
?>
