<section>
    <!-- Left Sidebar -->
    <aside id="leftsidebar" class="sidebar">
        <!-- Menu -->
        <div class="user-info bg-<?=THEME?>" style="background-image: none;">
            <div class="image">
                <img src="<?=BASE?>assets/images/user.png" width="48" height="48" alt="User">
            </div>
            <div class="info-container">
                <div class="name" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?=FULLNAME_USER?></div>
                <div class="btn-group user-helper-dropdown">
                    <i class="material-icons" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">keyboard_arrow_down</i>
                    <ul class="dropdown-menu pull-right">
                        <li><a href="<?=url('profile')?>" class=" waves-effect waves-block"><i class="material-icons">account_box</i><?=l('Update')?></a></li>
                        <li><a href="<?=url('logout')?>" class=" waves-effect waves-block"><i class="material-icons">lock_open</i><?=l('Logout')?></a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="menu bg-<?=THEME?>">
            <ul class="list">
                <li class="header"><hr></li>
                <li class="<?=(segment(1) == "dashboard")?"active":""?>">
                    <a href="<?=url('dashboard')?>">
                        <i class="material-icons">dashboard</i>
                        <span><?=l('Dashboard')?></span>
                    </a>
                </li>
                <li class="<?=(segment(1) == "logs")?"active":""?>">
                    <a href="<?=url('logs')?>">
                        <i class="material-icons">assignment_turned_in</i>
                        <span><?=l('Logs')?></span>
                    </a>
                </li>
                <li class="<?=(segment(1) == "proxy")?"active":""?>">
                    <a href="<?=url('payments')?>">
                        <i class="fa fa-shopping-cart fix-iconfa-sidebar" aria-hidden="true"></i>
                        <span><?=l('Comprar planos')?></span>
                    </a>
                </li>
                <li class="header"><hr></li>
                <li class="<?=(segment(1) == "instagram_accounts")?"active":""?>">
                    <a href="<?=url('instagram_accounts')?>">
                        <i class="fa fa-instagram fix-iconfa-sidebar" aria-hidden="true"></i>
                        <span><?=l('Instagram accounts')?></span>
                    </a>
                </li>
                <?php if(permission("activity/settings")){?>
                <li class="<?=(segment(2) == "settings")?"active":""?>">
                    <a href="<?=url('activity/settings')?>">
                        <i class="material-icons">settings</i>
                        <span><?=l('Start Automation')?></span>
                    </a>
                </li>
                <?php }?>
                <?php if(permission("activity")){
                    if (segment(2) != "settings"){?>
                <li class="<?=(segment(1) == "activity")?"active":""?>">
                    <?php 
                        }else{
                    ?>   
                <li>
                    <?php 
                }?>
                    <a href="<?=url('activity')?>">
                        <i class="material-icons">favorite_border</i>
                        <span><?=l('Atividades iniciadas')?></span>
                    </a>
                </li>
                <?php }?>
                <?php if(permission("post")){?>
                <li class="<?=(segment(1) == "post" || segment(2) == "post" || segment(1) == "save")?"active":""?>">
                    <a href="javascript:void(0);" class="menu-toggle">
                        <i class="material-icons">send</i>
                        <span><?=l("Auto post")?></span>
                    </a>
                    <ul class="ml-menu">
                        <li class="<?=(segment(1) == "post" && segment(2) != "bulk")?"active":""?>">
                            <a href="<?=url('post')?>">
                                <span><?=l('Add new')?></span>
                            </a>
                        </li>
                        <li class="<?=(segment(2) == "bulk")?"active":""?>">
                            <a href="<?=url('post/bulk')?>">
                                <span><?=l('Adicionar em massa')?></span>
                            </a>
                        </li>
                        <li class="<?=(segment(2) == "post")?"active":""?>">
                            <a href="<?=url('schedules/post')?>">
                                <span><?=l('Schedule post')?></span>
                            </a>
                        </li>
                        <li class="<?=(segment(1) == "save")?"active":""?>">
                            <a href="<?=url('save')?>">
                                <span><?=l('Save posts')?></span>
                            </a>
                        </li>
                    </ul>
                </li>
                <?php }?>
                <?php if(permission("message")){?>
                <li class="<?=(segment(1) == "message" || segment(2) == "message")?"active":""?>">
                    <a href="javascript:void(0);" class="menu-toggle">
                        <i class="material-icons">message</i>
                        <span><?=l("Auto direct message")?></span>
                    </a>
                    <ul class="ml-menu">
                        <li class="<?=(segment(1) == "message")?"active":""?>">
                            <a href="<?=url('message')?>">
                                <span><?=l('Add new')?></span>
                            </a>
                        </li>
                        <li class="<?=(segment(2) == "message")?"active":""?>">
                            <a href="<?=url('schedules/message')?>">
                                <span><?=l('Schedule message')?></span>
                            </a>
                        </li>
                    </ul>
                </li>
                <?php }?>
                <?php if(permission("search")){?>
                <li class="<?=(segment(1) == "search")?"active":""?>">
                    <a href="<?=url('search')?>">
                        <i class="material-icons">search</i>
                        <span><?=l('Instagram search')?></span>
                    </a>
                </li>
                <?php }?>
                <?php if(permission("download")){?>
                <li class="<?=(segment(1) == "download")?"active":""?>">
                    <a href="<?=url('download')?>">
                        <i class="material-icons">file_download</i>
                        <span><?=l('Instagram download')?></span>
                    </a>
                </li>
                <?php }?>

                <?php if(permission("", true)){?>
                <li class="header"><hr></li>
                <?php if(!hashcheck()){?>
                <li class="<?=(segment(1) == "package_settings" || segment(1) == "payment_settings" || segment(1) == "payment_history")?"active":""?>">
                    <a href="javascript:void(0);" class="menu-toggle">
                        <i class="fa fa-usd fix-iconfa-sidebar" aria-hidden="true"></i>
                        <span><?=l('Payment management')?></span>
                    </a>
                    <ul class="ml-menu">
                        <li class="<?=(segment(1) == "package_settings")?"active":""?>">
                            <a href="<?=url('package_settings')?>">
                                <span><?=l('Package settings')?></span>
                            </a>
                        </li>
                        <li class="<?=(segment(1) == "payment_history")?"active":""?>">
                            <a href="<?=url('payment_history')?>">
                                <span><?=l('Payment history')?></span>
                            </a>
                        </li>
                    </ul>
                </li>
                <?php }else{?>
                <li class="<?=(segment(1) == "package_settings")?"active":""?>">
                    <a href="<?=url('package_settings')?>">
                        <i class="fa fa-credit-card fix-iconfa-sidebar" aria-hidden="true"></i>
                        <span><?=l('Package settings')?></span>
                    </a>
                </li>
                <?php }?>
                <li class="<?=(segment(1) == "coupon")?"active":""?>">
                    <a href="<?=url('coupon')?>">
                        <i class="fa fa-ticket fix-iconfa-sidebar" aria-hidden="true"></i>
                        <span><?=l('Coupon management')?></span>
                    </a>
                </li>
                <li class="<?=(segment(1) == "user_management")?"active":""?>">
                    <a href="<?=url('user_management')?>">
                        <i class="fa fa-user fix-iconfa-sidebar" aria-hidden="true"></i>
                        <span><?=l('User management')?></span>
                    </a>
                </li>
                <li class="<?=(segment(1) == "settings")?"active":""?>">
                </li>
                <?php }?>
            </ul>
        </div>
        <!-- #Menu -->
        <!-- Footer -->
        <div class="legal">
            <div class="copyright">
                <a href="http://instaplus.com.br/painel/index.php/language?l=pt"><img src="http://instaplus.com.br/img/br-flag.png"></a>
                <a href="http://instaplus.com.br/painel/index.php/language?l=en"><img src="http://instaplus.com.br/img/ru-flag.png"></a>
            </div>
        </div>
        <!-- #Footer -->
    </aside>
    <!-- #END# Left Sidebar -->
</section>