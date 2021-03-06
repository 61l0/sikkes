<div id="tengah">
<div id="judul" class="title">
	<?php echo $title; ?>
</div>
<div id="content_tengah">
	<form class="appnitro" name="form_filtering" id="form_filtering" enctype="multipart/form-data" method="post" action="<?php echo base_url().'index.php/e-planning/prioritas/save_prioritas/'.$kdJenisPrioritas; ?>">
	<table width="100%" height="100%" cellspacing="0" cellpadding="0" >
		<tr>
			<td width="70%">Periode <?php echo form_dropdown('periode',$periode); ?></td>
		</tr>
		<tr>
			<td width="70%">
				<table width="100%" border="0" cellspacing="5" cellpadding="5" height="100%">
					<tr>
						<td>
							<div style="height:360px; overflow:auto;">
								<ul>
								<?php if($this->session->userdata('kd_role') == 8){ ?>
									<?php foreach($program->result() as $row){?>
									<li><input style="width:50px" type="checkbox" name="program[]" value="<?php echo $row->KodeProgram; ?>"><span><?php echo $row->KodeProgram." - ".$row->NamaProgram; ?></span>
										<ul>
											<li><strong>IKU</strong></li>
											<?php foreach($this->fm->get_where('ref_iku','KodeProgram',$row->KodeProgram)->result() as $row){?>
												<li><input style="width:50px" type="checkbox" name="iku[]" value="<?php echo $row->KodeIku; ?>" ><span><?php echo $row->KodeIku." - ".$row->Iku; ?></span>
											<?php } ?>
											<li><strong>Kegiatan</strong></li>
											<?php foreach($this->fm->get_where('ref_kegiatan','KodeProgram',$row->KodeProgram)->result() as $row){?>
											<li><input style="width:50px" type="checkbox" name="kegiatan[]" value="<?php echo $row->KodeKegiatan; ?>"><span><?php echo $row->KodeKegiatan." - ".$row->NamaKegiatan; ?></span>
												<ul>
													<?php foreach($this->fm->get_where('ref_ikk','KodeKegiatan',$row->KodeKegiatan)->result() as $row){?>
														<li><input style="width:50px" type="checkbox" name="ikk[]" value="<?php echo $row->KodeIkk; ?>"><span><?php echo $row->KodeIkk." - ".$row->Ikk; ?></span>
													<?php } ?>
												</ul>
											<?php } ?>
										</ul>
									<?php } ?>
								<?php } else { ?>
									<?php foreach($program->result() as $row){?>
									<li><input style="width:50px" type="checkbox" disabled="disabled" name="program[]" value="<?php echo $row->KodeProgram; ?>" <?php if($this->masmo->cek_beda('prioritas_program', $row->KodeProgram, 'KodeProgram', $kdJenisPrioritas, 'KodeJenisPrioritas', 1, 'kdsatker')) echo 'disabled = "TRUE"'; ?> <?php if($this->masmo->cek3('prioritas_program', 1, 'kdsatker', $row->KodeProgram, 'KodeProgram', $kdJenisPrioritas, 'KodeJenisPrioritas')) echo "checked=\"true\""; ?>><span><?php echo $row->KodeProgram." - ".$row->NamaProgram; ?></span>
										<ul>
											<li><strong>IKU</strong></li>
											<?php foreach($this->fm->get_where('ref_iku','KodeProgram',$row->KodeProgram)->result() as $row){?>
												<li><input style="width:50px" type="checkbox" disabled="disabled" name="iku[]" value="<?php echo $row->KodeIku; ?>" <?php if($this->masmo->cek_beda('prioritas_iku', $row->KodeIku, 'KodeIku', $kdJenisPrioritas, 'KodeJenisPrioritas', 1, 'kdsatker')) echo 'disabled = "TRUE"'; ?> <?php if($this->masmo->cek3('prioritas_iku', 1, 'kdsatker', $row->KodeIku, 'KodeIku', $kdJenisPrioritas, 'KodeJenisPrioritas')) echo "checked=\"true\""; ?>><span><?php echo $row->KodeIku." - ".$row->Iku; ?></span>
											<?php } ?>
											<li><strong>Kegiatan</strong></li>
											<?php foreach($this->fm->get_where('ref_kegiatan','KodeProgram',$row->KodeProgram)->result() as $row){?>
											<li><input style="width:50px" type="checkbox" disabled="disabled" name="kegiatan[]" value="<?php echo $row->KodeKegiatan; ?>" <?php if($this->masmo->cek_beda('prioritas_kegiatan', $row->KodeKegiatan, 'KodeKegiatan', $kdJenisPrioritas, 'KodeJenisPrioritas', 1, 'kdsatker')) echo 'disabled = "TRUE"'; ?> <?php if($this->masmo->cek3('prioritas_kegiatan', 1, 'kdsatker', $row->KodeKegiatan, 'KodeKegiatan', $kdJenisPrioritas, 'KodeJenisPrioritas')) echo "checked=\"true\""; ?>><span><?php echo $row->KodeKegiatan." - ".$row->NamaKegiatan; ?></span>
												<ul>
													<?php foreach($this->fm->get_where('ref_ikk','KodeKegiatan',$row->KodeKegiatan)->result() as $row){?>
														<li><input style="width:50px" type="checkbox" disabled="disabled" name="ikk[]" value="<?php echo $row->KodeIkk; ?>" <?php if($this->masmo->cek_beda('prioritas_ikk', $row->KodeIkk, 'KodeIkk', $kdJenisPrioritas, 'KodeJenisPrioritas', 1, 'kdsatker')) echo 'disabled = "TRUE"'; ?> <?php if($this->masmo->cek3('prioritas_ikk', 1, 'kdsatker', $row->KodeIkk, 'KodeIkk', $kdJenisPrioritas, 'KodeJenisPrioritas')) echo "checked=\"true\""; ?>><span><?php echo $row->KodeIkk." - ".$row->Ikk; ?></span>
													<?php } ?>
												</ul>
											<?php } ?>
										</ul>
									<?php } ?>
								<? } ?>
								</ul>
							</div>
						</td>
					</tr>
					<tr>
						<td>
						<?php if($this->session->userdata('kd_role') == 8){ ?>
							<div class="buttons">
								<button type="submit" class="normal" name="Cari">
									<img src="<?php echo base_url(); ?>images/main/save.png" alt=""/>
									Simpan
								</button>
								<button type="reset" class="negative" name="reset">
									<img src="<?php echo base_url(); ?>images/main/reset.png" alt=""/>
									Reset
								</button>
							</div>
						<?php } ?>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	</form>
</div>
</div>