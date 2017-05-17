<?php

namespace ApiV3\Controller;

use SendFileToClient\SendFileToClient;

class MediaController
    extends AbstractController
{

    public function get($id)
    {

        $where = array('filename' => $id);

        $mediaRenditionRepository = $this->getDoctrineRepository('\ApiV3\Entity\MediaRendition');
        $mediaRendition = $mediaRenditionRepository->findOneBy($where);

        if (empty($mediaRendition)) {
            return $this->_notFound();
        }

        $config = new \ApiV3\Module();
        $config = $config->getConfig();
        $path = sprintf(
            '%s/%s',
            $config['babelium']['path_uploads'],
            $mediaRendition->getFilename()
        );

        if (!file_exists($path)) {
            return $this->_notFound();
        }

        $send = new SendFileToClient();
        $send->sendFile($path, $mediaRendition->getFilename());
        die();

    }

}
