<?php

class Backlink_Monitoring_Manager_Loader_Validator {

    public static function link_validator( $toLink, $fromlink ) {
        
        $url = $fromlink;
        $response = wp_remote_get( $url );
        $urlHost = wp_parse_url( $toLink );

        if( ! is_wp_error( $response ) ) {
            
            if( is_array( $response ) ) {
                $response = $response['body'];
            }
    
            libxml_use_internal_errors( true );
            $doc = new \DOMDocument();
            $doc->loadHTML( $response );
            $links = [];
            $arr = $doc->getElementsByTagName('a');

            foreach ( $arr as $item ) {
                
                $href =  $item->getAttribute("href");
                $rel =  $item->getAttribute("rel");
                $text = trim( preg_replace( "/[\r\n]+/", " ", $item->nodeValue ) );

                if ( strpos( $href, $urlHost['host'] ) !== false ) {
                    $links = [ 'href' => $href, 'text' => $text, 'rel' => $rel ];
                    break;
                }
            }
            if( isset( $links['href'] ) && sizeof( $links ) > 0 ) {
                return $links;
            } else {
                $links = [ 'href' => '', 'text' => '', 'rel' => '' ];
            }
        } else {
            return false;
        }
    }
}