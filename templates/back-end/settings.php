<div class="wrap">
    <h2>Fast shop настройки</h2>
    <p>Вы можете изменять настройки во всех вкладках. После изменнения настроек не забудьте сохранить.</p>
    <form action="<?php echo wp_nonce_url($_SERVER['REQUEST_URI'],'fs_nonce'); ?>" method="post" class="fs-option">
   <div id="fs-options-tabs">
       <ul>
           <li><a href="#tabs-1">Общие настройки</a></li>
           <li><a href="#tabs-2">Письма</a></li>
           <li><a href="#tabs-3">Акции</a></li>


       </ul>
       <div id="tabs-1">
           <p>
               <label for="">Символ валюты <span>по умолчанию USD</span></label><br>
               <input type="text" name="fs_option['currency_symbol']">

           </p>
       </div>
       <div id="tabs-2">
           2
       </div>
       <div id="tabs-3">
           3
       </div>
   </div>
        <input type="submit" name="fs_save_options" value="Сохранение настроек">
    </form>
</div>