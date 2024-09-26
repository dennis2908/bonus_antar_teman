<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Http\Requests\StoreMenuRequest;
use App\Http\Requests\UpdateMenuRequest;
use Illuminate\Support\Facades\DB;

class BonusTemanController extends Controller
{
    public function index()
    {
        $data = DB::select('
            SELECT 
                "bkm_sites"."nama" AS site,    
                "bkm_do_kecil_header"."no_do_kecil" AS "do_kecil", 
                "bkm_do_kecil_header"."id" AS "do_kecil_id", 
                "bkm_do_kecil_header"."createdAt" AS "tanggal_do_kecil",
                "bkm_do_besar"."no_do_besar" AS "do_besar",
                "bkm_do_besar"."id" AS "do_besar_id",
                "bkm_komoditi"."nama_komoditi" AS "komoditi",
                "bkm_do_kecil_header"."no_spb",
                "bkm_user_karyawan"."nama" AS "driver",
                "bkm_kendaraan"."no_polisi" AS "nopol",
                "bkm_tipe_kendaraan"."tipe" AS "tipe_kendaraan",
                "bkm_customer_pks"."kode" AS "PKS",
                "bkm_tujuan_bongkar"."kode" AS "tujuan",
                "bkm_do_kecil_header"."tgl_muat" AS "tanggal_muat",
                "bkm_do_kecil_header"."bruto_muat",
                "bkm_do_kecil_header"."tarra_muat",
                "bkm_do_kecil_detail"."muat" AS "netto_muat",
                "bkm_do_kecil_header"."tgl_bongkar" AS "tanggal_bongkar",
                "bkm_do_kecil_header"."bruto_bongkar",
                "bkm_do_kecil_header"."tarra_bongkar",
                "bkm_do_kecil_detail"."bongkar" AS "netto_bongkar", 
                "bkm_do_kecil_detail"."bongkar" - "bkm_do_kecil_detail"."muat" AS "selisih",
                "bkm_ongkos_angkut"."ongkos_angkut",
                "bkm_do_kecil_detail"."muat" * "bkm_ongkos_angkut"."ongkos_angkut" AS "value_muat",
                "bkm_do_kecil_detail"."bongkar" * "bkm_ongkos_angkut"."ongkos_angkut" AS "value_bongkar",
                "bkm_do_kecil_header"."status"
                FROM bkm_do_kecil_header 
            LEFT JOIN "bkm_do_kecil_detail" ON "bkm_do_kecil_detail"."headerId" = "bkm_do_kecil_header"."id" 
            LEFT JOIN "bkm_do_besar" ON "bkm_do_besar"."id" = "bkm_do_kecil_detail"."dOBesarId" 
            LEFT JOIN "bkm_customer_pks" ON "bkm_customer_pks"."id" = "bkm_do_besar"."customerPKSId" 
            LEFT JOIN "bkm_komoditi" ON "bkm_komoditi"."id" = "bkm_do_besar"."komoditiId"
            LEFT JOIN "bkm_ongkos_angkut" ON "bkm_ongkos_angkut"."id" = "bkm_do_kecil_detail"."ongkosAngkutId"
            LEFT JOIN "bkm_kendaraan" ON "bkm_kendaraan"."id" = "bkm_do_kecil_header"."kendaraanId"
            LEFT JOIN "bkm_tipe_kendaraan" ON "bkm_tipe_kendaraan"."id" = "bkm_kendaraan"."tipeKendaraanId"
            LEFT JOIN "bkm_assign_driver_kendaraan" ON "bkm_assign_driver_kendaraan"."kendaraanId" = "bkm_do_kecil_header"."kendaraanId"
            LEFT JOIN "bkm_user_karyawan" ON "bkm_user_karyawan"."id" = "bkm_assign_driver_kendaraan"."karyawanId"
            LEFT JOIN "bkm_tujuan_bongkar" ON "bkm_tujuan_bongkar"."id" = "bkm_do_besar"."tujuanBongkarId"
            LEFT JOIN "bkm_sites" ON "bkm_sites"."id" = "bkm_customer_pks"."businessSiteId"
            WHERE "bkm_do_kecil_header"."status" NOT IN ('."'DELETIONAPPROVAL','DELETED','DECLINED','REJECTIONAPPROVAL','REJECTED'".')
                ');

                $denda_per_kg = 20000;

                $commodity_price = 10000;

                $cut_off_date_r1 = strtotime("2024-08-05");
                $cut_off_date_r2 = strtotime("2024-10-05");
                

                $i=0;

                $total_kontribusi_tidak_susut  = [];
                $total_denda_selisih  = [];

                foreach ($data as $v){

                    $data[$i]->batas_toleransi = ceil(($v->netto_muat*0.25/100)/10) * 10 *-1;

                    $data[$i]->denda_selisih = $v->netto_bongkar - $v->netto_muat;

                    $penalty = $denda_per_kg * ( $data[$i]->denda_selisih - $data[$i]->batas_toleransi) * (-1);

                    $data[$i]->kontribusi_tidak_susut = $data[$i]->denda_selisih - $data[$i]->batas_toleransi;

                    if(($data[$i]->denda_selisih - $data[$i]->batas_toleransi) < 0)
                         $data[$i]->kontribusi_tidak_susut = 0;


                    if(strtotime($v->tanggal_muat) >= $cut_off_date_r1 && strtotime($v->tanggal_muat) <= $cut_off_date_r2) {
                    
                        if(!isset($total_kontribusi_tidak_susut[$v->do_besar_id])){
                                    $total_kontribusi_tidak_susut[$v->do_besar_id] = $data[$i]->kontribusi_tidak_susut;    
                        }    
                        else{
                                $total_kontribusi_tidak_susut[$v->do_besar_id]+=$data[$i]->kontribusi_tidak_susut;
                        }
                        
                        $data[$i]->total_kontribusi_tidak_susut =  $total_kontribusi_tidak_susut[$v->do_besar_id];
                    } 
                    else
                        $data[$i]->total_kontribusi_tidak_susut =  0;
                    
                    if($data[$i]->total_kontribusi_tidak_susut > 0)    
                        $data[$i]->bonus_contribution = $data[$i]->kontribusi_tidak_susut / $data[$i]->total_kontribusi_tidak_susut;
                    else
                        $data[$i]->bonus_contribution = 0;
                    
                    $data[$i]->denda_fr  = $data[$i]->total_kontribusi_tidak_susut * $commodity_price * 3; 
                    
                    if(strtotime($v->tanggal_muat) >= $cut_off_date_r1 && strtotime($v->tanggal_muat) <= $cut_off_date_r2) {
                    
                        if(!isset($total_denda_selisih [$v->do_besar_id])){
                            $total_denda_selisih[$v->do_besar_id] = $data[$i]->denda_selisih ;    
                        }    
                        else{
                            $total_denda_selisih[$v->do_besar_id]+=$data[$i]->denda_selisih ;
                        }
                        
                        $data[$i]->total_denda_selisih  =  $total_denda_selisih[$v->do_besar_id];
                    } 
                    else
                        $data[$i]->total_denda_selisih =  0;

                    $data[$i]->total_bonus = $data[$i]->total_denda_selisih - $data[$i]->denda_fr;
                    $data[$i]->bonus_antar_teman  = $data[$i]->bonus_contribution * $data[$i]->total_bonus;
                    unset($v->no_spb);
                    unset($v->nopol);
                    unset($v->tipe_kendaraan);
                    unset($v->tujuan);
                    unset($v->brutto_muat);
                    unset($v->status);
                    unset($v->tarra_muat);
                    unset($v->brutto_bongkar);
                    unset($v->tarra_bongkar);
                    unset($v->ongkos_angkut);
                    $i++;
                }    

		return response()->json(['result'=>$data]);
    }
}
