<?php defined('BASEPATH') OR exit('No direct script access allowed');


use Dompdf\Dompdf;
use Dompdf\Options;

class Tec_dompdf
{

    public function generate($content, $name = 'download.pdf', $output_type = null, $footer = null, $margin_bottom = null, $header = null, $margin_top = null, $orientation = 'P') {
        $html = '';

        if (is_array($content)) {
            foreach ($content as $page) {
                $html .= $header ? '<header>' . $header . '</header>' : '';
                $html .= '<footer>'.($footer ? $footer.'<br><span class="pagenum"></span>' : '<span class="pagenum"></span>').'</footer>';
                $html .= '<div class="page">'.$page['content'].'</div>';
            }
        } else {
            $html .= $header ? '<header>' . $header . '</header>' : '';
            $html .= $footer ? '<footer>' . $footer . '</footer>' : '';
            $html .= $content;
        }

        $options = new Options();
        $options->set('isPhpEnabled', true); // Enable PHP inside HTML
        $options->set('isRemoteEnabled', true);
        // You can adjust other options as needed
        $dompdf = new Dompdf($options);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', ($orientation == 'P' ? 'portrait' : 'landscape'));

        // Font subsetting is enabled by default in newer versions, but you can explicitly set it if needed
        $dompdf->render();

        if ($output_type == 'S') {
            $output = $dompdf->output();
            write_file('assets/uploads/' . $name, $output);
            return 'assets/uploads/' . $name;
        } else {
            $dompdf->stream($name);
            return true;
        }
    }
}
