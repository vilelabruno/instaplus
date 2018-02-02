<div class="row SchedulesListActivity" data-action="<?=url("schedules/ajax_enable_activity")?>">
    <div class="col-md-12">
        <div class="filter">
            <div class="row">
                <div class="col-lg-5 col-md-5 col-sm-12 col-xs-12">
                    <div class="form-group">
                        <a href="<?=cn()."settings"?>" class="btn bg-light-green btn-lg waves-effect"><i class="fa fa-plus" aria-hidden="true"></i> <?=l('Configurar nova conta')?></a>
                    </div>
                </div>
                <form>
                    <div class="col-lg-2 col-md-2 col-sm-2 col-xs-6">
                        <div class="input-group">
                            <span class="input-group-addon"><?=l('Sort:')?> </span>
                            <select name="sort" class="form-filter form-control show-tick activity_speed">
                                <option value=""><?=l('-')?></option>
                                <option value="username" <?=get("sort") == "username"?"selected":""?>><?=l('Username')?></option>
                                <option value="time" <?=get("sort") == "time"?"selected":""?>><?=l('Time')?></option>
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-2 col-sm-2 col-xs-6">
                        <div class="input-group">
                            <span class="input-group-addon"><?=l('Filter:')?> </span>
                            <select name="filter" class="form-filter form-control show-tick activity_speed">
                                <option value=""><?=l('-')?></option>
                                <option value="started" <?=get("filter") == "started"?"selected":""?>><?=l('Started')?></option>
                                <option value="stoped" <?=get("filter") == "stoped"?"selected":""?>><?=l('Stoped')?></option>
                                <option value="notime" <?=get("filter") == "notime"?"selected":""?>><?=l('No time')?></option>
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12">
                        <div class="input-group mb15">
                            <div class="form-line">
                                <input type="text" name="keyword" class="form-control" value="<?=clean(get("keyword"))?>" placeholder="<?=l('Search by username')?>">
                            </div>
                            <span class="input-group-btn">
                              <button type="submit" class="btn bg-blue waves-effect"><i class="fa fa-search" aria-hidden="true"></i> <?=l('Search')?></button>
                            </span>
                        </div>
                    </div>
                </form>
            </div>
            <div class="clearfix"></div>
        </div>
    </div>
    <div class="clearfix"></div>

    <?php if(!empty($result)){
    foreach ($result as $key => $row) {
    ?>
    <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12">
        <div class="item" data-id="<?=$row->id?>" data-action="<?=cn("disconnect")?>">
            <?php if($row->checkpoint == 1){?>
            <div class="checkpoint btn-danger text-center">
                <b><?=l("Activity Stoped Working");?></b><br/>
                <?=l("Please go to <a href='http://instagram.com/' target='_blank'>http://instagram.com/</a> to verify email and then click button MORE and select RECONNECT");?>
            </div>
            <?php }?>

            <?php if($row->checkpoint == 2){?>
            <div class="checkpoint btn-danger text-center">
                <b><?=l("Activity Stoped Working");?></b><br/>
                <?=l("Please update password on this account to continue use");?>
            </div>
            <?php }?>

            <?php if($row->checkpoint == 3){?>
            <div class="checkpoint btn-danger text-center">
                <b><?=l("Activity Stoped Working");?></b><br/>
                <?=l("Please check proxy and then click button MORE and select RECONNECT");?>
            </div>
            <?php }?>
            <div class="info-box-2 mb0">
                <i class="fa fa-instagram" aria-hidden="true"></i>
                <div class="icon">
                    <img style="margin: 9px;" class="media-object" src="<?=$row->avatar?>" width="64" height="64">
                </div>
                <div class="content">
                    <div class="text"><?=l('Instagram')?></div>
                    <div class="number count-to" data-from="0" data-to="125" data-speed="1000" data-fresh-interval="20"><?=$row->account_name?></div>
                </div>
            </div>
            <div class="list-group">
                <a href="javascript:void(0);" class="list-group-item status ajax_status">
                    <?php
                    switch ($row->status) {
                        case 5:
                            echo '<span class="badge bg-light-green">'.l('Started').'</span>';
                            break;

                        default:
                            echo '<span class="badge btn-danger">'.l('Stoped').'</span>';
                            break;
                    }
                    ?>
                    <?=l('Activity')?>
                </a>
                <a href="javascript:void(0);" class="list-group-item">
                    <span class="badge bg-light-grey disabled"><?=$row->like_count?></span> <?=l('Likes')?>
                </a>
                <a href="javascript:void(0);" class="list-group-item">
                    <span class="badge bg-light-grey disabled"><?=$row->comment_count?></span> <?=l('Comments')?>
                </a>
                <a href="javascript:void(0);" class="list-group-item">
                    <span class="badge bg-light-grey disabled"><?=$row->follow_count?></span> <?=l('Follows')?>
                </a>
                <a href="javascript:void(0);" class="list-group-item">
                    <span class="badge bg-light-grey disabled"><?=$row->like_follow_count?></span> <?=l('Like + Follows')?>
                </a>
                <a href="javascript:void(0);" class="list-group-item">
                    <span class="badge bg-light-grey disabled"><?=$row->followback_count?></span> <?=l('Follow back')?>
                </a>
                <a href="javascript:void(0);" class="list-group-item">
                    <span class="badge bg-light-grey disabled"><?=$row->unfollow_count?></span> <?=l('Unfollows')?>
                </a>
                <a href="javascript:void(0);" class="list-group-item">
                    <span class="badge bg-light-grey disabled"><?=$row->repost_count?></span> <?=l('Repost')?>
                </a>
                <a href="javascript:void(0);" class="list-group-item">
                    <span class="badge bg-light-grey disabled"><?=$row->deletemedia_count?></span> <?=l('Delete media')?>
                </a>
                <div class="list-group-item control">
                    <span class="ajax_btn_enable">
                    <?php
                    switch ($row->status) {
                        case 5:
                            echo '<button type="button" class="btn btn-danger btn-lg waves-effect uc btnActivityAll" style="width: 28%;">'.l('Stop').'</button>';
                            break;

                        default:
                            echo '<button type="button" class="btn bg-light-green btn-lg waves-effect uc btnActivityAll" style="width: 28%;">'.l('Start').'</button>';
                            break;
                    }
                    ?>

                    </span>
                    <a href="<?=cn()."settings?id=".$row->id?>" class="btn bg-blue btn-lg waves-effect uc" style="width: 40%;"><?=l('Settings')?></a>
                    <div class="btn-group" style="width: 28%;">
                        <button type="button" class="btn bg-orange btn-lg dropdown-toggle uc" style="width: 100%;" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                            <?=l('More')?> <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a href="<?=PATH."logs?account=".$row->account_id?>" class=" waves-effect waves-block"><?=l('Log')?></a></li>
                            <li><a href="https://instagram.com/<?=$row->account_name?>" target="_blank" class=" waves-effect waves-block"><?=l('Profile')?></a></li>
                            <li role="separator" class="divider"></li>
                            <li><a href="javascript:void(0);" class=" waves-effect waves-block btnReconnect btnUpdateGroups" data-type="page" data-id="<?=$row->account_id?>" data-action-groups="<?=url('instagram_accounts/ajax_get_groups')?>"><?=l('Reconnect')?></a></li>
                            <li><a href="javascript:void(0);" class=" waves-effect waves-block btnDisconnect" data-confirm="<?=l('Do you want disconect this account?')?>"><?=l('Disconnect')?></a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php }}else{?>
        <div class="col-md-12">
            <div class="logs_empty">
                <i class="fa fa-chain-broken" aria-hidden="true"></i>
                <div class="text"><?=l('No recent activity')?></div>
            </div>
        </div>
    <?php }?>
</div>
