<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if(!defined("IN_VIEW"))
    exit;
?>
<div class="logout_container">
    <p class="logout_text">
        <a href="?logout">Sign out</a>
    </p>
</div>