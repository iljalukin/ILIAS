<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificatePdfFilename implements ilCertificateFilename

{
	public function createFileName(string $objectInformation, string $userName)
	{
		$pdfDownloadName = $objectInformation . ' ' . $userName .' Certificate';

		return $pdfDownloadName;
	}

}
