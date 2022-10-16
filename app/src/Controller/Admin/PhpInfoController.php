<?php

namespace App\Controller\Admin;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class PhpInfoController extends DashboardController
{
    #[IsGranted('ROLE_SUPER_ADMIN')]
    #[Route('/admin/phpinfo', name: 'admin_phpinfo')]
    public function phpinfo(): Response
    {
        ob_start();
        phpinfo(INFO_GENERAL);
        phpinfo(INFO_CONFIGURATION);
        phpinfo(INFO_MODULES);

        $output = ob_get_contents();
        ob_get_clean();

        $output = str_replace('body {background-color: #fff;', 'body {', $output);
        $output = str_replace('hr {width: 934px; background-color: #ccc; border: 0; height: 1px;}', '', $output);
        $output = str_replace('a:link {color: #009; text-decoration: none; background-color: #fff;}', '', $output);
        $output = "
            <style type='text/css'>
                #phpinfo {}
                #phpinfo pre {margin: 0; font-family: monospace;}
                #phpinfo a:link {color: #009; text-decoration: none; background-color: #fff;}
                #phpinfo a:hover {text-decoration: underline;}
                #phpinfo table {border-collapse: collapse; border: 0; width: 934px; box-shadow: 1px 2px 3px #ccc;}
                #phpinfo .center {text-align: center;}
                #phpinfo .center table {margin: 1em auto; text-align: left;}
                #phpinfo .center th {text-align: center !important;}
                #phpinfo td, th {border: 1px solid #666; font-size: 75%; vertical-align: baseline; padding: 4px 5px;}
                #phpinfo h1 {font-size: 150%;}
                #phpinfo h2 {font-size: 125%;}
                #phpinfo .p {text-align: left;}
                #phpinfo .e {background-color: #ccf; width: 300px; font-weight: bold;}
                #phpinfo .h {background-color: #99c; font-weight: bold;}
                #phpinfo .v {background-color: #ddd; max-width: 300px; overflow-x: auto; word-wrap: break-word;}
                #phpinfo .v i {color: #999;}
                #phpinfo img {float: right; border: 0;}
                #phpinfo hr {width: 934px; background-color: #ccc; border: 0; height: 1px;}
                h1.p{ font-size: 500% !important; }
            </style>
            <div id='phpinfo'>
                $output
            </div>
            ";
        return $this->render('@EasyAdmin/pages/phpinfo.html.twig', ['phpinfo' => $output]);
    }
}
