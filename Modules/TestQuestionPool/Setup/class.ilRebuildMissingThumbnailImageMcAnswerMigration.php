<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

use ILIAS\Setup;
use ILIAS\Setup\Environment;
use ILIAS\Setup\CLI\IOWrapper;

class ilRebuildMissingThumbnailImageMcAnswerMigration implements Setup\Migration
{
    private ilDBInterface $db;
    private bool $verbose = false;

    public function getLabel(): string
    {
        return 'Rebuild Missing Thumbnail Images for Multiple Choice Question Answers';
    }

    public function getDefaultAmountOfStepsPerRun(): int
    {
        return 10;
    }

    public function getPreconditions(Environment $environment): array
    {
        return [
            new \ilDatabaseInitializedObjective()
        ];
    }

    public function prepare(Environment $environment): void
    {
        ilContext::init(ilContext::CONTEXT_CRON);
        ilInitialisation::initILIAS();
        $this->db = $environment->getResource(Environment::RESOURCE_DATABASE);
        $this->verbose = $environment->getResource(Environment::RESOURCE_ADMIN_INTERACTION)->isVerbose();
    }

    public function step(Environment $environment): void
    {
        if (!defined("ILIAS_HTTP_PATH")) {
            define("ILIAS_HTTP_PATH",
                ilUtil::_getHttpPath()); // _getHttpPath is a method that reads http_path from ini-file
        }

        $query = 'select qst.question_fi, qst.thumb_size, a.imagefile from qpl_qst_mc qst inner join qpl_a_mc a on qst.question_fi=a.question_fi where qst.thumb_size is null and LENGTH(TRIM(a.imagefile)) > 0 group by qst.question_fi limit 1';
        $result = $this->db->query($query);
        while ($question = $this->db->fetchAssoc($result)) {
            $question_obj = assMultipleChoice::_instanciateQuestion((int) $question['question_fi']);
            $imageWidth = $this->rebuildThumbnails($question_obj);
            $this->db->replace(
                $question_obj->getAdditionalTableName(),
                [
                    'thumb_size' => array('integer', $imageWidth),
                ],
                ['question_fi' => array('integer', $question_obj->getId())]
            );

            $this->log("\nUpdated question " . $question_obj->getId() . " with thumbnail width " . $imageWidth . "\n");
        }
    }

    public function getRemainingAmountOfSteps(): int
    {
        $query = 'select count(distinct qst.question_fi) as cnt from qpl_qst_mc qst inner join qpl_a_mc a on qst.question_fi=a.question_fi where qst.thumb_size is null and LENGTH(TRIM(a.imagefile)) > 0';
        $result = $this->db->query($query);
        $row = $this->db->fetchAssoc($result);

        return (int) ($row['cnt'] ?? 0);
    }

    protected function generateThumbForFile($question, $file): int
    {
        $filename = $question->getImagePath() . $file;
        if (@file_exists($filename) && filesize($filename) > 0) {
            $thumbpath = $question->getImagePath() . $question->getThumbPrefix() . $file;
            $this->log("\nGenerating thumbnail for image " . $filename . " with path " . $thumbpath . " for question " . $question->getId());
            $path_info = @pathinfo($filename);
            $ext = "";
            switch (strtoupper($path_info['extension'])) {
                case 'PNG':
                    $ext = 'PNG';
                    break;
                case 'GIF':
                    $ext = 'GIF';
                    break;
                default:
                    $ext = 'JPEG';
                    break;
            }
            $imageInfo = getimagesize($filename);
            $imageWidth = $question->getThumbSize();

            if ($imageInfo) {
                $imageWidth = $imageInfo[0];
            }
            ilShellUtil::convertImage($filename, $thumbpath, $ext, (string) $imageWidth);
            return $imageWidth;
        }
        $this->log("\nWARNING: Could not determine image width for image " . $filename . " of question " . $question->getId() . "\n");
        return 0;
    }

    protected function rebuildThumbnails($question): int
    {
        $imageWidth = 0;
        if ($question->isSingleline()) {
            foreach ($question->getAnswers() as $answer) {
                if ($answer->getImage()) {
                    $width = $this->generateThumbForFile($question, $answer->getImage());
                    $this->log("\nRebuilt thumbnail for image " . $answer->getImage() . " of answer " . $answer->getId() . " with width " . $width . " for question " . $question->getId());
                    if ($width > $imageWidth) {
                        $imageWidth = $width;
                    }
                }
            }
        }
        return $imageWidth;
    }

    public function log($message): void
    {
        if ($this->verbose) {
            echo $message;
        }
    }
}