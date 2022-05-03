			<?php
			$this->lang->load('ps', 'english');
			?>
			<ul class="breadcrumb">
				<li><a href="<?php echo site_url(). "/dashboard";?>"><?php echo $this->lang->line('dashboard_label')?></a> <span class="divider"></span></li>
				<li><a href="<?php echo site_url('items');?>"><?php echo $this->lang->line('item_list_label')?></a> <span class="divider"></span></li>
				<li><?php echo $this->lang->line('add_new_item_button')?></li>
			</ul>
			<div class="wrapper wrapper-content animated fadeInRight">
			<?php
			$attributes = array('id' => 'item-form','enctype' => 'multipart/form-data');
			echo form_open(site_url('items/add'), $attributes);
			?>
				<legend><?php echo $this->lang->line('item_info_label')?></legend>
					
				<div class="row">
					<div class="col-sm-6">
							<div class="form-group">
								<label><?php echo $this->lang->line('item_name_label')?>
									<a href="#" class="tooltip-ps" data-toggle="tooltip" title="<?php echo $this->lang->line('item_name_tooltips')?>">
										<span class='glyphicon glyphicon-info-sign menu-icon'>
									</a>
								</label>
								<input class="form-control" type="text" placeholder="<?php echo $this->lang->line('item_name_label')?>" name='name' id='name'>
							</div>
							
							<div class="form-group">
								<label><?php echo $this->lang->line('cat_label')?>
									<a href="#" class="tooltip-ps" data-toggle="tooltip" title="<?php echo $this->lang->line('cat_tooltips')?>">
										<span class='glyphicon glyphicon-info-sign menu-icon'>
									</a>
								</label>
								<select class="form-control" name="cat_id" id="cat_id">
								<option><?php echo $this->lang->line('select_cat_message')?></option>
								<?php
									$categories = $this->category->get_all($this->city->get_current_city()->id);
									foreach($categories->result() as $cat)
										echo "<option value='".$cat->id."'>".$cat->name."</option>";
								?>
								</select>
							</div>
							
							<div class="form-group">
								<label><?php echo $this->lang->line('sub_cat_label')?>
									<a href="#" class="tooltip-ps" data-toggle="tooltip" title="<?php echo $this->lang->line('sub_cat_tooltips')?>">
										<span class='glyphicon glyphicon-info-sign menu-icon'>
									</a>
								</label>
								<select class="form-control" name="sub_cat_id" id="sub_cat_id">
									<option><?php echo $this->lang->line('select_sub_cat_message')?></option>
								</select>
							</div>

							<div class="form-group">
								<label><?php echo $this->lang->line('phone_label')?> 
									<a href="#" class="tooltip-ps" data-toggle="tooltip" title="<?php echo $this->lang->line('phone_tooltips')?>">
								    	<span class='glyphicon glyphicon-info-sign menu-icon'>
									</a>
								</label>
								<input class="form-control" type="text" name="phone" placeholder="<?php echo $this->lang->line('phone_label')?> "/>
							</div>

							<div class="form-group">
								<label><?php echo $this->lang->line('email_label')?> 
									<a href="#" class="tooltip-ps" data-toggle="tooltip" title="<?php echo $this->lang->line('email_tooltips')?>">
								    	<span class='glyphicon glyphicon-info-sign menu-icon'>
									</a>
								</label>
								<input class="form-control" type="text" name="email" placeholder="<?php echo $this->lang->line('email_label')?> "/>
							</div>

							<div class="form-group">
								<label><?php echo $this->lang->line('lat_label')?> 
									<a href="#" class="tooltip-ps" data-toggle="tooltip" title="<?php echo $this->lang->line('lat_tooltips')?>">
								    	<span class='glyphicon glyphicon-info-sign menu-icon'>
									</a>
								</label>
								<input class="form-control" type="text" name="lat" placeholder="<?php echo $this->lang->line('lat_label')?> "/>
							</div>

							<div class="form-group">
								<label><?php echo $this->lang->line('lng_label')?> 
									<a href="#" class="tooltip-ps" data-toggle="tooltip" title="<?php echo $this->lang->line('lng_tooltips')?>">
								    	<span class='glyphicon glyphicon-info-sign menu-icon'>
									</a>
								</label>
								<input class="form-control" type="text" name="lng" placeholder="<?php echo $this->lang->line('lng_label')?> "/>
							</div>
							
					</div>
					
					<div class="col-sm-6">
							
							<div class="form-group">
								<label><?php echo $this->lang->line('search_tag_label')?> 
									<a href="#" class="tooltip-ps" data-toggle="tooltip" title="<?php echo $this->lang->line('search_tag_tooltips')?>">
								    	<span class='glyphicon glyphicon-info-sign menu-icon'>
									</a>
								</label>
								<input class="form-control" type="text" name="search_tag" placeholder="<?php echo $this->lang->line('search_tag_label')?> "/>
							</div>
							
							<div class="form-group">
								<label><?php echo $this->lang->line('description_label')?> 
									<a href="#" class="tooltip-ps" data-toggle="tooltip" title="<?php echo $this->lang->line('item_description_tooltips')?>">
										<span class='glyphicon glyphicon-info-sign menu-icon'>
									</a>
								</label>
								<textarea class="form-control" name="description" placeholder="<?php echo $this->lang->line('description_label')?>" rows="8"></textarea>
							</div>
							
							<div class="form-group">
								<label><?php echo $this->lang->line('address_label')?> 
									<a href="#" class="tooltip-ps" data-toggle="tooltip" title="<?php echo $this->lang->line('address_tooltips')?>">
										<span class='glyphicon glyphicon-info-sign menu-icon'>
									</a>
								</label>
								<textarea class="form-control" name="address" placeholder="<?php echo $this->lang->line('address_label')?>" rows="8"></textarea>
							</div>
					</div>
				</div>
				
				<input type="submit" name="save" value="<?php echo $this->lang->line('save_button')?>" class="btn btn-primary"/>
				<input type="submit" name="gallery" value="<?php echo $this->lang->line('save_go_button')?>" class="btn btn-primary"/>
				<a href="<?php echo site_url('items');?>" class="btn btn-primary"><?php echo $this->lang->line('cancel_button')?></a>
			</form>
			</div>
			<script>
			$(document).ready(function(){
				$('#item-form').validate({
					rules:{
						name:{
							required: true,
							minlength: 4,
							remote: {
								url: '<?php echo site_url("items/exists");?>',
					        	type: "GET",
					        	data: {
					        		name: function () {
					        			return $('#name').val();
					        		},
					          	sub_cat_id: function() {
					          		return $('#sub_cat_id').val();
					          	}
					        	}
							}
						},
						unit_price: {
							number: true
						}
					},
					messages:{
						name:{
							required: "Please fill item Name.",
							minlength: "The length of item Name must be greater than 4",
							remote: "Item Name is already existed in the system"
						},
						unit_price: {
							number: "Only number is allowed."
						}
					}
				});
				
				$('#cat_id').change(function(){
					var catId = $(this).val();
					$.ajax({
						url: '<?php echo site_url('items/get_sub_cats');?>/'+catId,
						method: 'GET',
						dataType: 'JSON',
						success:function(data){
							$('#sub_cat_id').html("");
							$.each(data, function(i, obj){
							    $('#sub_cat_id').append('<option value="'+ obj.id +'">' + obj.name + '</option>');
							});
							$('#name').val($('#name').val() + " ").blur();
						}
					});
				});
				
				$('#sub_cat_id').on('change', function(){
					$('#name').val($('#name').val() + " ").blur();
				});
				
				$(function () { $("[data-toggle='tooltip']").tooltip(); });
			});
			</script>

