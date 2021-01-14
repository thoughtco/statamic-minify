<?php

namespace Thoughtco\Minify\Listeners;

use File;
use MatthiasMullie\Minify;
use Statamic\Events\ResponseCreated;

class MinifyListener
{

	private $minPath = 'min/';

    /**
     * before response sent back to browser
     */
    public function handle(ResponseCreated $event)
    {
        $content = $event->response->content();
        if (stripos($content, '<html') !== false)
            $event->response->setContent($this->parseForMinifiableFiles($content));
    }

    // parse for css/js
    protected function parseForMinifiableFiles($response)
    {
		// linkgroups as we match by media type
		$cssGroups = array();

		// link groups to remove
		$linkElements = array();
		$groupedLinkElements = array();

		// matches to remove
		$removeCSS = $removeCSSGroups = $removeJS = $removeJSGroups = array();

		// match link[href]
		preg_match_all('/<link([^>]+)href="([^"]+)"([^>]*)>/i', $response, $linkMatches, PREG_OFFSET_CAPTURE);
		if (count($linkMatches) > 0){

			foreach ($linkMatches[0] as $i=>$match){

				// ignore if IE blocks
				if (stripos($match[0], 'rel="stylesheet') !== FALSE && preg_match('/IE([^>]*)>/i', substr($response, $match[1] - 6, 7)) !== 1){

					preg_match('/media="([^"]+)"/i', $match[0], $mediaMatch);
					if (count($mediaMatch) > 1){

						if (strpos($linkMatches[2][$i][0], '//') === FALSE){

							// no grouping
							if (strpos($linkMatches[0][$i][0], 'data-group') === FALSE){

								$cssGroups[$mediaMatch[1]][] = $linkMatches[2][$i][0];
								$removeCSS[] = $match[0];

							// grouping
							} else {

								preg_match_all('/data-group="([^"]+)"/i', $linkMatches[0][$i][0], $groupMatch);

								// make an array
								if (!isset($cssGroups[$groupMatch[1][0]][$mediaMatch[1]])){
									$cssGroups[$groupMatch[1][0]][$mediaMatch[1]] = array();
								}

								$cssGroups[$groupMatch[1][0]][$mediaMatch[1]][] = $linkMatches[2][$i][0];
								$removeCSSGroups[$groupMatch[1][0]][] = $match[0];

							}

						}

					}

				}

			}
		}

		// match script[src]
		preg_match_all('/<script([^>]+)src="([^"]+)"([^>]*)><\/script>/i', $response, $scriptMatches);
		if (count($scriptMatches) > 0){
			foreach ($scriptMatches[0] as $i=>$match){

				if (strpos($scriptMatches[2][$i], '//') === FALSE && strpos($scriptMatches[2][$i], '://') === FALSE){

					// no grouping
					if (strpos($scriptMatches[0][$i], 'data-group') === FALSE){

						$linkElements[] = $scriptMatches[2][$i];
						$removeJS[] = $match;

					// grouping
					} else {

						preg_match_all('/data-group="([^"]+)"/i', $scriptMatches[0][$i], $groupMatch);

						$groupedLinkElements[$groupMatch[1][0]][] = $scriptMatches[2][$i];
						$removeJSGroups[$groupMatch[1][0]][] = $match;

					}

				}

			}
		}

		// do we need to make min dir?
		$minPath = public_path($this->minPath);
		if (!is_dir($minPath)){
			File::makeDirectory($minPath, $mode = 0777, true, true);
		}

		$replacementCSS = array();
		$replacementCSSGrouped = array();

		// loop over css groups
		foreach ($cssGroups as $media=>$files){

			// styles may be grouped
			$filesNoSubgroups = array();
			foreach ($files as $index=>$file){
				if (is_array($file)){

					$tag = $this->combineAndMinify($minPath, $file, 'css');
					if ($tag != ''){
						$replacementCSSGrouped[$media][] = '<link rel="stylesheet" type="text/css" href="'.$this->minPath.$tag['file'].'.css?'.$tag['version'].'" media="'.$index.'" />';
					}

				} else {
					$filesNoSubgroups[] = $file;
				}

			}

			// or not
			$tag = $this->combineAndMinify($minPath, $filesNoSubgroups, 'css');
			if ($tag != ''){
				$replacementCSS[] = '<link rel="stylesheet" type="text/css" href="'.$this->minPath.$tag['file'].'.css?'.$tag['version'].'" media="'.$media.'" />';
			}

		}


		// if we have css to replace
		if (count($replacementCSS) > 0){

			// no grouping
			if (count($removeCSS) > 0){
				$response = str_replace($removeCSS[0], implode("\n\t", $replacementCSS), $response);
			}

			// replace first instance of group with combined
			foreach ($removeCSSGroups as $index=>$group){
				$response = str_replace($group[0], implode("\n\t", $replacementCSSGrouped[$index]), $response);
			}

		}

		// loop over js
		if (count($linkElements) > 0){
			$tag = $this->combineAndMinify($minPath, $linkElements, 'js');
			if ($tag != ''){
				$response = str_replace($removeJS[0], '<script type="text/javascript" src="'.$this->minPath.$tag['file'].'.js?'.$tag['version'].'"></script>', $response);
			}
		}

		// loop over grouped js
		if (count($groupedLinkElements) > 0){

			foreach ($groupedLinkElements as $group=>$linkElements){

				$tag = $this->combineAndMinify($minPath, $linkElements, 'js');
				if ($tag != ''){
					$response = str_replace($removeJSGroups[$group][0], '<script type="text/javascript" src="'.$this->minPath.$tag['file'].'.js?'.$tag['version'].'"></script>', $response);
				}

			}

		}

		// remove everything
		foreach ($removeCSS as $el){
			$response = str_replace(array($el."\n", $el."\r", $el), '', $response);
		}

		foreach ($removeCSSGroups as $index=>$els){
			foreach ($els as $el){
				$response = str_replace(array($el."\n", $el."\r", $el), '', $response);
			}
		}

		foreach ($removeJS as $el){
			$response = str_replace(array($el."\n", $el."\r", $el), '', $response);
		}

		foreach ($removeJSGroups as $index=>$els){
			foreach ($els as $el){
				$response = str_replace(array($el."\n", $el."\r", $el), '', $response);
			}
		}

		return $response;
    }

    // combine and minify files
	protected function combineAndMinify($minPath, $aFiles, $type)
    {

		// get file last modified dates
		$aLastModifieds = array();
		foreach ($aFiles as $sFile){
			$aLastModifieds[] = File::lastModified(public_path($sFile));
		}

		if (count($aLastModifieds) < 1) return;

		// sort dates, newest first
		rsort($aLastModifieds);

		// get array of filenames
		$vals = array_values($aFiles);

		// alphabetic sort
		sort($vals);

		// create an md5 of the filenames
		$md5files = md5(implode('', $vals));

		// extension
		$extension = ($type == 'css' ? 'css' : 'js');

		// create a unique tag, last modified file
		$iETag = $md5files;

		// archive name
		$sMergedFilename = $minPath.'/'.$iETag.'.'.$extension;

		// we already have this archive
		if (File::exists($sMergedFilename)) {

			if (File::lastModified($sMergedFilename) >= $aLastModifieds[0]){
				return array('file' => $iETag, 'version' => $aLastModifieds[0]);
			}

		}

		// get merge code
		$sCode = '';
		$sCodeAfter = '';

		// type?
		if ($type == 'js'){
			$minifier = new Minify\JS();
		} else {
			$minifier = new Minify\CSS();
		}

		foreach ($aFiles as $sFile) {
			$minifier->add(public_path($sFile));
		}

		$sCode = $minifier->minify();

		// write file
		File::put($sMergedFilename, $sCode);

		// get time
		$time = File::lastModified($sMergedFilename);

		return array('file' => $iETag, 'version' => $time);
	}
}

?>
