<?php

namespace ApiV3\Services;

class SubTitles
{

    protected $_formatTime = 'H:i:s';

    /**
     * Devuelve los segundos de los subtitulos, en un formato correcto para
     * que lo interprete correctamente el archivo .vtt
     *
     * @param unknown $seconds
     * @return string
     */
    public function getFormatTimeBySeconds($seconds)
    {

        $values = explode('.', $seconds);
        $second = reset($values);

        $hideTime = new \DateTime("@$second");
        return $hideTime->format($this->_formatTime) . '.000';

    }

    /**
     * Extrae los subtitulos en un array, para poder generar el archivo .vtt
     *
     * @param array $subtitle
     *         Información de los subtitulos guardados en Base de datos.
     * @return array
     */
    public function parseSubtitles(array $subtitle = array())
    {

        $parsedSubtitles = array();
        $distinctVoices = array();

        $serialized = $this->unpackblob($subtitle['serialized_subtitles']);
        $subtitles = \Zend\Json\Json::decode($serialized, true);

        foreach ($subtitles as $num => $data) {

            $sline = new \stdClass();
            $sline->id = $num;
            $sline->showTime = $data['start_time'] / 1000;
            $sline->hideTime = $data['end_time'] / 1000;
            $sline->text = $data['text'];

            $sline->exerciseRoleName = $data['meta']['voice'];
            $sline->subtitleId = $subtitle['id'];

            $c = count($distinctVoices);
            if (!array_key_exists($data['meta']['voice'], $distinctVoices)) {
                $distinctVoices[$data['meta']['voice']] = $c+1;
            }
            $sline->exerciseRoleId = $distinctVoices[$data['meta']['voice']];

            $parsedSubtitles[] = $sline;

        }

        return $parsedSubtitles;

    }

    /**
     * Unserializes and uncompresses the given data.
     *
     * @param string $data
     *         Base64 encoded and compressed data.
     * @return string
     *         The decoded string or the original parameter if any decoding step was unsuccessful.
     */
    private function unpackblob($data)
    {
        if (($decoded = base64_decode($data)) !== FALSE) {
            if (($plaindata = gzuncompress($decoded)) !== FALSE) {
                return $plaindata;
            }
        }
        return $data;
    }

}