<?php

namespace cav;

class Register
{
   public function __construct()
   {
      add_action('wp_head', [$this, 'add_head'], 25);
   }

   public function add_head()
   {
      ?>
<style>
   * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
   }

   .with-border{
      --padding-y: 2px;
   }

   body {
      background-color: #131314;
      color: #fff;
      font-family: ui-sans-serif, system-ui, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol', 'Noto Color Emoji';
      font-size: 1.1rem
   }

   h1 {
      font-size: 2rem;
      text-align: center;
   }

   a {
      color: inherit;
      text-decoration: none;
   }

   header {
      margin-top: 4rem;
   }

   .list {
      list-style: none;
      display: flex;
      flex-direction: column;
      gap: 1rem;
      justify-content: center;
      margin: 3rem auto;
      max-width: 360px;
   }

   .list svg {
      width: 32px;
   }

   .item {
      position: relative;
   }

   .box {
      position: absolute;
      z-index: 1;
      background-color: #fff;
      border-radius: .5rem;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      transition: top 300ms,left 300ms;
   }

   .item:hover .box {
    top: 5px;
    left: 5px;
   }

   .link {
      align-items: center;
      border-color: transparent;
      border-radius: .5rem;
      border-style: solid;
      border-width: 2px;
      display: flex;
      font-weight: 600;
      gap: .5rem;
      justify-content: center;
      padding: calc(7px + var(--padding-y, 0px)) 1rem;
      position: relative;
      text-align: center;
      z-index: 5;
   }

   .link.with-border{
   background-color:#131314;
   border-color: #fff;

   }

</style>
<?php
   }
}
?>
