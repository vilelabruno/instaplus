<script>(function(d,t,u,s,e){e=d.getElementsByTagName(t)[0];s=d.createElement(t);s.src=u;s.async=1;e.parentNode.insertBefore(s,e);})(document,'script','//instaplus.com.br/livechat//php/app.php?widget-init.js');</script>
<nav class="navbar">
    <div class="container<?=session("uid")?"-fluid":""?>">
        <div class="navbar-header">
            <a href="javascript:void(0);" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse" aria-expanded="false"></a>
            <?php if(session("uid")){?>
            <a href="javascript:void(0);" class="bars"></a>
            <?php }?>
            <a class="navbar-brand text-center" href="<?=PATH?>"><img src="<?=LOGO?>" title="" alt=""></a>
        </div>
        
        <div class="collapse navbar-collapse" id="navbar-collapse">
            <ul class="nav navbar-nav top-menu right mr0">
                <li>
                    <?php if(session("tmp_uid")) { ?>
                    <div class="btn-group" style="margin-top: 7px; margin-left: 7px;">
                        <button type="button" class="btn btn-danger waves-effect col-white btnActionBackAdmin" data-action="<?=url("user_management/ajax_action_back_admin")?>"><?=l("Back to Admin")?></button>
                    </div>
                    <?php } ?>
                </li>
            </ul>
        </div>
    </div>
</nav>