			<?php
			$this->lang->load('ps', 'english');
			?>
			<ul class="breadcrumb">
				<li><a href="<?php echo site_url(). "/dashboard";?>"><?php echo $this->lang->line('dashboard_label')?></a> <span class="divider"></span></li>
				<li><a href="<?php echo site_url('users');?>"><?php echo $this->lang->line('users_list_label')?></a> <span class="divider"></span></li>
				<li><?php echo $this->lang->line('add_new_user_button')?></li>
			</ul>
			<div class="wrapper wrapper-content animated fadeInRight">
				<?php
				$attributes = array('id' => 'user-form');
				echo form_open(site_url('users/add'), $attributes);
				?>
					<legend><?php echo $this->lang->line('user_info_label')?></legend>
						
					<div class="row">
						<div class="col-sm-6">
								<div class="form-group">
									<label><?php echo $this->lang->line('username_label')?></label>
									<input class="form-control" type="text" placeholder="Username" name='user_name' id='user_name'>
								</div>
								
								<div class="form-group">
									<label><?php echo $this->lang->line('email_label')?></label>
									<input class="form-control" type="text" placeholder="Email" name='user_email' id='user_email'>
								</div>
								
								<div class="form-group">
									<label><?php echo $this->lang->line('password_label')?></label>
									<input class="form-control" type="password" placeholder="Password" name='user_password' id='user_password'>
								</div>
											
								<div class="form-group">
									<label><?php echo $this->lang->line('confirm_password_label')?></label>
									<input class="form-control" type="password" placeholder="Confirm Password" name='conf_password' id='conf_password'>
								</div>
								
								<div class="form-group">
									<label><?php echo $this->lang->line('user_role_label')?></label>
									<select class="form-control" name='role_id' id='role_id'>
										<?php
											foreach($this->role->get_all()->result() as $role)
												echo "<option value='".$role->role_id."'>".$role->role_desc."</option>";
										?>
									</select>
								</div>
								<hr>
								<div class="form-group">
									<label><?php echo $this->lang->line('select_city_label')?></label> <br>
									<select class="form-control" name='city_id' id='city_id'>
										<?php
											foreach($this->city->get_all()->result() as $city)
												echo "<option value='".$city->id."'>".$city->name."</option>";
										?>
									</select>
								</div>
								
								
						</div>
						
						<div class="col-sm-6">
							<div class="form-group">
								<label><?php echo $this->lang->line('allowed_modules_label')?></label>
								<?php
									foreach($this->module->get_all()->result() as $module)
										echo "<label class='checkbox'><input type='checkbox' name='permissions[]' value='".$module->module_id."'>".$module->module_desc."</label><br/>";
								?>
							</div>
						</div>
					</div>
					
					<hr/>
					
					<button type="submit" class="btn btn-primary"><?php echo $this->lang->line('save_button')?></button>
					<a href="<?php echo site_url('users');?>" class="btn btn-primary"><?php echo $this->lang->line('cancel_button')?></a>
				</form>
			</div>
			<script>
			$(document).ready(function(){
				$('#user-form').validate({
					rules:{
						user_name:{
							required: true,
							minlength: 4,
							remote: '<?php echo site_url("users/exists");?>'
						},
						user_email:{
							required: true,
							email: true
						},
						user_password:{
							required: true,
							minlength: 4
						},
						conf_password:{
							required: true,
							equalTo: '#user_password'
						}
					},
					messages:{
						user_name:{
							required: "Please fill user name.",
							minlength: "The length of username must be greater than 4",
							remote: "Username is already existed in the system"
						},
						user_email:{
							required: "Please fill email address",
							email: "Please provide valid email address"
						},
						user_password:{
							required: "Please fill user password.",
							minlength: "The length of password must be greater than 4"
						},
						conf_password:{
							required: "Please fill confirm password",
							equalTo: "Password and confirm password do not match."
						}
					}
				});
			});
		</script>

