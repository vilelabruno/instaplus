    
    <?php if($count < getMaximumAccount() || !empty($result)){?>
        <div class="card">
            <div class="header">
                <h2>
                    <i class="fa fa-plus-square" aria-hidden="true"></i> <?=l('Instagram account')?> 
                </h2>
            </div>
            <div class="body">
                <div class="row">
                    <div class="col-sm-12 mb0">
                        <form action="<?=url('instagram_accounts/ajax_update')?>" data-redirect="activity/settings">
                            <b><?=l('Instagram username')?> (<span class="col-red">*</span>)</b>
                            <div class="form-group">
                                <div class="form-line">
                                    <input type="hidden" class="form-control" name="id" value="<?=!empty($result)?$result->id:0?>">
                                    <input type="text" class="form-control" name="username" placeholder="<?=l('Username')?> ">
                                </div>
                            </div>
                            <b><?=l('Instagram password')?> (<span class="col-red">*</span>)</b>
                            <div class="form-group">
                                <div class="form-line">
                                    <input type="password" class="form-control" name="password" placeholder="<?=l('Password')?> ">
                                </div>
                            </div>

                            <?php if(session('admin')==1){ ?>
                            <div class="form-group">
                                <select name="proxy" style="display: none;" class="form-control" >
                                    <option value=""><?=l('Select proxy')?></option>
                                    <?php if(!empty($proxy)){

                                    foreach ($proxy as $row) {
                                    ?>
                                    
                                    <?php }}?>
                                    <option value="0" selected><?=l('Using IP Host (Not recommend)')?></option>
                                </select>
                            </div>



                            <?php }else{ 
                                if(!empty($proxy)&&session('admin')!=1){
                            ?>
                            <div class="form-group">
                                <select name="proxy" style="display: none;" class="form-control" >
                                    <option value="0" selected><?=l('Using IP Host (Not recommend)')?></option>
                                    <?php 
                                    foreach ($proxy as $row) {
                                    ?>
                                    
                                    <?php }?>
                                </select>
                            </div>
                            <?php }} ?>

                            <button type="submit" onclick="closeModal()" class="btn bg-light-green waves-effect btnIGAccountUpdate"><?=l('Connect Instagram')?></button>
                            <script type="text/javascript">
                                function closeModal(){
                                    $('#modal-add-account').modal('hide');
                                }
                            </script>
                            <div class="alert mb15 mt15" style="color: #000!important">
                                <i class="fa fa-check-circle-o col-green" aria-hidden="true"></i> <?=l('A autenticação de 2 fatores precisa estar desabilitada para conectar sua conta do Instagram.')?><br/>
                                <i class="fa fa-check-circle-o col-green" aria-hidden="true"></i> <?=l('Make sure that e-mail, which you used for registration in Instagram is real, and you have access to it.')?><br/>
                                <i class="fa fa-check-circle-o col-green" aria-hidden="true"></i> <?=l('E-mail, which you used for registration in Instagram is confirmed and approved by Instagram.')?><br/>
                                <i class="fa fa-check-circle-o col-green" aria-hidden="true"></i> <?=l('It is strongly recommended to bind your Instagram account to any Facebook page.')?><br/>
                                <i class="fa fa-check-circle-o col-green" aria-hidden="true"></i> <?=l('Upload avatar and fill in your profile.')?><br/>
                                <i class="fa fa-check-circle-o col-green" aria-hidden="true"></i> <?=l('Add at least 6 photos to your account from mobile phone.')?><br/>
                                <i class="fa fa-check-circle-o col-green" aria-hidden="true"></i> <?=l('Ensure that your content does not violate the rules of Instagram. We are not responsible for your actions and their consequences.')?><br/>
                                <i class="fa fa-check-circle-o col-green" aria-hidden="true"></i> <?=l('We are not responsible for your actions and their consequences.')?><br/>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php }else{?>
    <div class="card">
        <div class="body">
            <div class="alert alert-danger">
                <?=l('Oh sorry! You have exceeded the number of accounts allowed, You are only allowed to update your account')?>
            </div>
            <a href="<?=cn()?>" class="btn bg-grey waves-effect"><?=l('Back')?></a>
        </div>
    </div>
    <?php }?>
    </div>

