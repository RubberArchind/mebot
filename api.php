<?php
function get_domain($url)
{
    $pieces = parse_url($url);
    $domain = isset($pieces['host']) ? $pieces['host'] : $pieces['path'];
    if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i',
        $domain, $regs)) {
        return $regs['domain'];
    }
    return false;
}

function file_get_contents_curl($url)
{
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

    $data = curl_exec($ch);
    curl_close($ch);

    return $data;
}

function checkurlpath($url)
{
    if (!filter_var($url, FILTER_VALIDATE_URL) === false) {
        return true;
    } else {
        return false;
    }
}

function checkinstaurl($urlhere, $redirectpath)
{
    //remove white space
    $urlhere = trim($urlhere);
    $urlhere = htmlspecialchars($urlhere);
    ///remove white space

    if (get_domain($urlhere) == "instagram.com") {
        //Its is a instagram url
        if (checkurlpath($urlhere)) {

            //getting the meta tag data

            $html = file_get_contents_curl($urlhere);

            //parsing begins here:
            $doc = new DOMDocument();
            @$doc->loadHTML($html);
            $nodes = $doc->getElementsByTagName('title');

            //get and display what you need:
            $title = $nodes->item(0)->nodeValue;

            $metas = $doc->getElementsByTagName('meta');
            $mediatype = null;
            $description = null;

            for ($i = 0; $i < $metas->length; $i++) {
                $meta = $metas->item($i);

                if ($meta->getAttribute('property') == 'og:type') {
                    $mediatype = $meta->getAttribute('content');
                }

                if ($mediatype == 'video') {
                    if ($meta->getAttribute('property') == 'og:video') {
                        $description = $meta->getAttribute('content');
                    }

                } else {
                    if ($meta->getAttribute('property') == 'og:image') {
                        $description = $meta->getAttribute('content');
                    }

                    $mediatype = 'photo';
                }

            } // for loop statement
            $out['mediatype'] = $mediatype;
            $out['descriptionc'] = $description;

            return $out;

            ///getting the meta tag data

        } // if the url path is right
        else {
            redirecterror($redirectpath);
        }
    } else {
        redirecterror($redirectpath);
    }

}

$res=checkinstaurl($_GET['url'], "https://villahollanda.com/api.php?error=true");
echo json_encode($res);
