<?php

namespace App\Enums\OccurrenceEnums;

enum OccurrenceType: string
{
    CASE URBAN_FIRE = 'incendio_urbano';
    CASE VEHICLE_RESCUE = 'resgate_veicular';
    CASE PRE_HOSPITAL_CARE = 'atendimento_pre_hospitalar';
    CASE WATER_RESCUE = 'salvamento_aquatico';
    CASE FALSE_ALERT = 'falso_chamado';
}
