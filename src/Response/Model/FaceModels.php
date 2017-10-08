<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class FaceModels extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'face_align_model'  => '',
        'face_detect_model' => '',
        'pdm_multires'      => '',
    ];
}
