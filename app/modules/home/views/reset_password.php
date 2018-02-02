<div class="box-login">
  <div class="login-form">
    <div class="clearfix"></div>
    <form  action="<?=url('user_management/ajax_reset_password')?>" data-redirect="<?=url("?st=success")?>">
        <input type="hidden" class="form-control" name="reset_key" value="<?=get("key")?>">
        <div class="input-group">
            <span class="input-group-addon">
                <i class="material-icons">lock</i>
            </span>
            <div class="form-line">
                <input type="password" class="form-control" name="password" minlength="6" placeholder="<?=l('Password')?>" required>
            </div>
        </div>
        <div class="input-group">
            <span class="input-group-addon">
                <i class="material-icons">lock</i>
            </span>
            <div class="form-line">
                <input type="password" class="form-control" name="repassword" minlength="6" placeholder="<?=l('Confirm password')?>" required>
            </div>
        </div>
        <div class="input-group">
            <button type="submitt" class="right btn bg-light-green waves-effect btnActionUpdate"><?=l('Reset password')?></button>
        </div>
    </form>
    <a href="<?=url("login")?>"><?=l('Login')?></a>
  </div>
  <div class="copyright"><?=l('2016 - 2017 © VTCreators. All rights reserved.')?></div>
</div>