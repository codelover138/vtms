<?php defined('BASEPATH') OR exit('No direct script access allowed');



use Zend\Barcode\Barcode;

class Tec_barcode
{
    public function __construct() {
    }

    public function __get($var) {
        return get_instance()->$var;
    }

    public function generate($text, $bcs = 'code128', $height = 50, $drawText = true, $get_be = false, $re = false) {
        // Prepare the text and barcode settings
        $check = $this->prepareForChecksum($text, $bcs);

        // Options for generating the barcode
        $barcodeOptions = [
            'text' => $check['text'],
            'barHeight' => $height,
            'drawText' => $drawText,
            'withChecksum' => $check['checksum'],
            'withChecksumInText' => $check['checksum']
        ];

        // Check if the setting prefers PNG images
        if ($this->Settings->barcode_img) {
            $rendererOptions = [
                'imageType' => 'png',
                'horizontalPosition' => 'center',
                'verticalPosition' => 'middle'
            ];

            // Render or output directly for PNG
            if ($re) {
                Barcode::render($bcs, 'image', $barcodeOptions, $rendererOptions);
                exit;
            }

            // Generate barcode as image resource
            $imageResource = Barcode::draw($bcs, 'image', $barcodeOptions, $rendererOptions);

            // Capture the image output buffer
            ob_start();
            imagepng($imageResource);
            $imagedata = ob_get_contents();
            ob_end_clean();

            // Return base64-encoded image if requested
            if ($get_be) {
                return 'data:image/png;base64,' . base64_encode($imagedata);
            }

            // Return HTML image tag with the base64-encoded PNG
            return "<img src='data:image/png;base64," . base64_encode($imagedata) . "' alt='{$text}' class='bcimg' />";
        } else {
            // If SVG format is preferred
            $rendererOptions = [
                'renderer' => 'svg',
                'horizontalPosition' => 'center',
                'verticalPosition' => 'middle'
            ];

            // Render or output directly for SVG
            if ($re) {
                Barcode::render($bcs, 'svg', $barcodeOptions, $rendererOptions);
                exit;
            }

            // Capture the SVG output buffer
            ob_start();
            Barcode::render($bcs, 'svg', $barcodeOptions, $rendererOptions);
            $imagedata = ob_get_contents();
            ob_end_clean();

            // Return base64-encoded SVG if requested
            if ($get_be) {
                return 'data:image/svg+xml;base64,' . base64_encode($imagedata);
            }

            // Return HTML image tag with the base64-encoded SVG
            return "<img src='data:image/svg+xml;base64," . base64_encode($imagedata) . "' alt='{$text}' class='bcimg' />";
        }

        return false;
    }

    protected function prepareForChecksum($text, $bcs) {
        if ($bcs == 'code25' || $bcs == 'code39') {
            return ['text' => $text, 'checksum' => false];
        } elseif ($bcs == 'code128') {
            return ['text' => $text, 'checksum' => true];
        }
        return ['text' => substr($text, 0, -1), 'checksum' => true];
    }
}
