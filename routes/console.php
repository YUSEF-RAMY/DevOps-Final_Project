<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('pure-rose:send-occasion-reminders')->dailyAt('09:00');
