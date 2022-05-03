			<?php
			$this->lang->load('ps', 'english');
			?>
			<ul class="breadcrumb">
				<li><a href="<?php echo site_url('cities');?>"><?php echo $this->lang->line('cities_list_label')?></a> <span class="divider"></span></li>
				<li><?php echo $this->lang->line('city_info_label')?></li>
			</ul>
		
			<!-- Message -->
			<?php if($this->session->flashdata('success')): ?>
				<div class="alert alert-success fade in">
					<?php echo $this->session->flashdata('success');?>
					<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
				</div>
			<?php elseif($this->session->flashdata('error')):?>
				<div class="alert alert-danger fade in">
					<?php echo $this->session->flashdata('error');?>
					<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
				</div>
			<?php endif;?>
			
			<?php
			$attributes = array('id' => 'city-form','enctype' => 'multipart/form-data');
			echo form_open(site_url("cities/create"), $attributes);
			?>
				<div class="row">
					
					<ul id="myTab" class="nav nav-tabs">
					   <li class="active"><a href="#cityinfo" data-toggle="tab"><?php echo $this->lang->line('city_info_label')?></a></li>
					</ul>
					
					<div id="myTabContent" class="tab-content">
						<div class="tab-pane fade in active" id="cityinfo">
							<br>
							<div class="form-group">
								<label><?php echo $this->lang->line('name_label'); ?>
									<a href="#" class="tooltip-ps" data-toggle="tooltip" title="<?php echo $this->lang->line('city_name_tooltips')?>">
										<span class='glyphicon glyphicon-info-sign menu-icon'>
									</a>
								</label>
								<input class="form-control" type="text" placeholder="Name" name='name' id='name'>
							</div>
							
							<div class="form-group">
								<label><?php echo $this->lang->line('description_label')?>
									<a href="#" class="tooltip-ps" data-toggle="tooltip" title="<?php echo $this->lang->line('city_desc_tooltips')?>">
										<span class='glyphicon glyphicon-info-sign menu-icon'>
									</a>
								</label>
								<textarea class="form-control" name="description" placeholder="Description" rows="9"></textarea>
							</div>
							
							<div class="form-group">
								<label><?php echo $this->lang->line('city_lat_label')?>
									<a href="#" class="tooltip-ps" data-toggle="tooltip" title="<?php echo $this->lang->line('city_lat_tooltips')?>">
										<span class='glyphicon glyphicon-info-sign menu-icon'>
									</a>
								</label><br>
								<input class="form-control" type="text" placeholder="Latitude" name='lat' id='lat'>
							</div>
							
							<div class="form-group">
								<label><?php echo $this->lang->line('city_lng_label')?>
									<a href="#" class="tooltip-ps" data-toggle="tooltip" title="<?php echo $this->lang->line('city_lng_tooltips')?>">
										<span class='glyphicon glyphicon-info-sign menu-icon'>
									</a>
								</label><br>
								<input class="form-control" type="text" placeholder="Longitude" name='lng' id='lng'>
							</div>
								
							<div class="form-group">
								<label><?php echo $this->lang->line('location_label')?>
									<a href="#" class="tooltip-ps" data-toggle="tooltip" title="<?php echo $this->lang->line('city_location_tooltips')?>">
										<span class='glyphicon glyphicon-info-sign menu-icon'>
									</a>
								</label>
								<textarea class="form-control" name="address" placeholder="Location" rows="5"></textarea>
							</div>
							
							<div class="form-group">
								<label><?php echo $this->lang->line('city_cover_photo_label')?>
									<a href="#" class="tooltip-ps" data-toggle="tooltip" title="<?php echo $this->lang->line('city_photo_tooltips')?>">
										<span class='glyphicon glyphicon-info-sign menu-icon'>
									</a>
								</label> 
								<br>
								<?php echo $this->lang->line('city_image_recommended_size')?>
								<input class="btn" type="file" name="images1">
								<br/>
								<label><?php echo $this->lang->line('photo_desc_label')?> 
									<a href="#" class="tooltip-ps" data-toggle="tooltip" title="<?php echo $this->lang->line('city_photo_desc_tooltips')?>">
									<span class='glyphicon glyphicon-info-sign menu-icon'>
									</a></label>
								<textarea class="form-control" name="image_desc" rows="9"></textarea>
							</div>
						</div>
					
				   </div>		
				</div>
				
				<hr/>
				
				<button type="submit" class="btn btn-primary"><?php echo $this->lang->line('save_button')?></button>
				
				<a href="<?php echo site_url('cities');?>" class="btn btn-primary"><?php echo $this->lang->line('cancel_button')?></a>
			</form>
			<script>
				$(document).ready(function(){
					$(function () { $("[data-toggle='tooltip']").tooltip(); });
				});
				
				$('#city-form').validate({
					rules:{
						name:{
							required: true
						},
						description:{
							required : true
						},
						lat:{
							required : true
						},
						lng:{
							required : true
						},
						email: {
							required: true,
							email: true
						}
					},
					messages:{
						name:{
							required: "Please Fill City Name."
						},
						description:{
							required: "Please Fill City Description."
						},
						lat:{
							required: "Please Fill City Lattitude."
						},
						lng:{
							required: "Please Fill City Longitude."
						},
						email: {
							email: "Email format is wrong.",
							required : "Please Fill Email."
						}
					}
				});	
			</script>