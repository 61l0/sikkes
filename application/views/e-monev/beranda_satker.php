<style>
#ver-minimalist
{
	font-family: "Lucida Sans Unicode", "Lucida Grande", Sans-Serif;
	font-size: 12px;
	width: 900px;
	text-align: center;
	border-top:1px #333 solid;
	border-left:1px #333 solid;
}

#ver-minimalist td
{
	padding:5px;
	border-right:1px #333 solid;
	border-bottom:1px #333 solid;
	vertical-align:middle;
	color:#333;
}

.text-bold {
    font-weight:bold;
}

.text-title {
    height:30px;
}
</style>

    <div id="tengah">
        <div id="judul" class="title">Progress Satker Propinsi <?php echo ucwords(strtolower($propinsi)); ?> <!--{elapsed_time}--></div>
        <br />
    <div>
</div>
	<div id="content_tengah">
	    <table id="ver-minimalist" cellpadding="0" cellspacing="0">
          <tr>
            <td colspan="16" class="text-bold text-title">PROGRES SATKER PROPINSI <?php echo $propinsi; ?></td>
          </tr>
          <tr>
            <td rowspan="3" class="text-bold">No</td>
            <td rowspan="3" class="text-bold">NAMA PROPINSI</td>
            <td rowspan="3" class="text-bold">TOTAL</td>
            <td width="15" rowspan="<?php echo $satker->num_rows()+4; ?>">&nbsp;</td>
            <td colspan="11" class="text-bold">
				<?php 
                    $bulan_angka = $this->uri->segment(5);
                    if($bulan_angka != NULL){
                        if($bulan_angka >= 1 && $bulan_angka <= 12){
                            if($bulan_angka == 1){
                                $bulan_angka_minus = 12;
                                $bulan_angka_plus = $bulan_angka + 1;
                            }elseif($bulan_angka == 12){
                                $bulan_angka_minus = $bulan_angka - 1;
                                $bulan_angka_plus = 1;
                            }else{
                                $bulan_angka_minus = $bulan_angka - 1;
                                $bulan_angka_plus = $bulan_angka + 1;
                            }
                        }else{
                            $bulan_angka_minus = date("n");
                            $bulan_angka_plus = date("n");
                        }
                    }else{
                        $bulan_angka_minus = date("n");
                        $bulan_angka_plus = date("n");
                        $bulan_angka = date("n");;
                    }
                    
                    $bulan_huruf = $this->general->konversi_bulan($bulan_angka);
					$kodeunit = $this->uri->segment(6);
                ?>
                <a href="<?php echo base_url();?>index.php/e-monev/beranda/detail_satker/<?php echo $idlokasi; ?>/<?=$bulan_angka_minus;?>/<?=$kodeunit;?>"><<</a> 
                &nbsp; <? echo $bulan_huruf; ?> &nbsp; 
                <a href="<?php echo base_url();?>index.php/e-monev/beranda/detail_satker/<?php echo $idlokasi; ?>/<?=$bulan_angka_plus;?>/<?=$kodeunit;?>">>></a>
			</td>
            <td width="15" rowspan="<?php echo $satker->num_rows()+4; ?>">&nbsp;</td>
          </tr>
          <tr>
            <td colspan="5" class="text-bold">KEUANGAN</td>
            <td colspan="5" class="text-bold">FISIK</td>
          </tr>
          <tr>
            <td class="text-bold">MERAH</td>
            <td class="text-bold">KUNING</td>
            <td class="text-bold">HIJAU</td>
            <td class="text-bold">BIRU</td>
            <td class="text-bold">PROGRES</td>            
            <td class="text-bold">MERAH</td>
            <td class="text-bold">KUNING</td>
            <td class="text-bold">HIJAU</td>
            <td class="text-bold">BIRU</td>
            <td class="text-bold">PROGRES</td>
          </tr>
          <?php echo $list_satker;  ?>
          <tr>
            <td colspan="3" align="right" class="text-bold">PROGRES PROPINSI</td>
            <td colspan="4"><?php echo $total_item_semua; ?></td>
            <td><?php echo round($total_progres_keuangan); ?>%</td>
            <td colspan="4"><?php echo $total_item_semua; ?></td>
            <td><?php echo round($total_progres_fisik); ?>%</td>
          </tr>
        </table>
        
        <br /><br />
        <div class="buttons">
            <a href="<?php echo base_url();?>index.php/e-monev/beranda/detail_propinsi/<?=$bulan_angka?>/<?=$kodeunit?>"><img src="<?php echo base_url(); ?>images/main/back.png" alt=""/>Back</a>
        </div>
        &nbsp;&nbsp;&nbsp;&nbsp;<a href="#tengah">^ scroll ke Atas</a>
    </div>
</div>
</div>
