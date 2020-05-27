<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://example.com
 * @since             1.0.0
 * @package           Plugin_Name
 *
 * @wordpress-plugin
 * Plugin Name:       Tickets to Dolibarr
 * Plugin URI:        http://example.com/plugin-name-uri/
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            Consultups
 * Author URI:        http://example.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       plugin-name
 * Domain Path:       /languages
 */

add_action( 'wpcf7_before_send_mail', 'wpcf7_add_text_to_mail_body' );

function wpcf7_add_text_to_mail_body($contact_form){
    if($contact_form->id == 750){
        $contact_form->skip_mail = true;
        $fp = fopen('data.txt', 'w');
        //fwrite($fp, $contact_form->id());
        //$wpcf7      = WPCF7_ContactForm::get_current();
        $submission = WPCF7_Submission::get_instance();

        if($submission){
            $data = $submission->get_posted_data();
            //fwrite($fp, $data["phone"]);


            // CREAR TERCERO SI NO EXISTE
            /*$url = "https://erp.consultups.tech/api/index.php/thirdparties?sortfield=t.rowid&sortorder=ASC&limit=1&sqlfilters=(t.phone%3Alike%3A'".$data['phone']."')";

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, 'DOLAPIKEY: r26sfbjk');
            $result = curl_exec($ch);

            var_dump($result);*/
//            $solicitud->subject = "duda";
//            $solicitud->message = $data["asunto"]." ".$data["phone"];
//            $solicitud->name = $data["nombre"];
//            $solicitud = json_encode($solicitud);
//            fwrite($fp, $solicitud);

            $message = $data["asunto"];
            $phone = $data["phone"];
            $name = $data["nombre"];










            $url = "https://erp.consultups.tech/api/index.php/thirdparties?sortfield=t.rowid&sortorder=ASC&limit=1&sqlfilters=(t.phone%3Alike%3A'".$phone."')";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('DOLAPIKEY: r26sfbjk'));
//header('Content-type: application/json');
            ob_start();

            $result = curl_exec($ch);

            $content = ob_get_clean();
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            curl_close($ch);

            if($httpcode != 200){
                // USER NOT EXISTS. CREATE IT.
                $solicitud = [
                    "name" => $name,
                    "phone"=> $phone
                ];

                $solicitud = json_encode($solicitud);

                fwrite($fp,$solicitud);


                $url = "https://erp.consultups.tech/api/index.php/thirdparties";
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt( $ch, CURLOPT_POSTFIELDS, $solicitud );
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Accept: application/json', 'DOLAPIKEY: r26sfbjk'));
                curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

                $result = curl_exec($ch);

                curl_close($ch);

                //GET NEW USER
                $url = "https://erp.consultups.tech/api/index.php/thirdparties?sortfield=t.rowid&sortorder=ASC&limit=1&sqlfilters=(t.phone%3Alike%3A'".$phone."')";

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('DOLAPIKEY: r26sfbjk'));
                //header('Content-type: application/json');
                ob_start();

                $result = curl_exec($ch);

                $content = ob_get_clean();
                $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                curl_close($ch);
            }


            // USER EXISTS

            //GET ID AND INSERT TICKET (id in thirds is ref. In tickets is socid)
            $content = json_decode($content);
            $content = $content[0];
            $id = (int)$content->ref;
            $name = $content->name;
            $phone = $content->phone;
            //var_dump($content);
            //echo $id;
            //echo $content[0]->phone;

            $solicitud = [
                "subject" => "Solicitud automática - WordPress",
                "message" => "Un usuario (".$name.") ha contactado con soporte para asistencia. Su teléfono es: ".$phone.". Y su mensaje dice: ".$message,
                "socid"   => $id,
                "fk_soc"  => $id
            ];

            $solicitud = json_encode($solicitud);

            // REALIZAMOS PETICIÓN POST
            $url = "https://erp.consultups.tech/api/index.php/tickets";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt( $ch, CURLOPT_POSTFIELDS, $solicitud );
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Accept: application/json', 'DOLAPIKEY: r26sfbjk'));
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

            $result = curl_exec($ch);

            curl_close($ch);
            // echo $solicitud;



            return $content;













        }



        //$phone = strtolower($contact_form->prop('phone'));

        fclose($fp);
    }

}