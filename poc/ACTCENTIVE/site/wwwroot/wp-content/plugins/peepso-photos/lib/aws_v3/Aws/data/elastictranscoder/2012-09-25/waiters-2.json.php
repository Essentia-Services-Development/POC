<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3UjlpMFRQUGE2Q3RtRTRtVUdhU3oydys2K3lhTUpuOVZhM2tUazdlUC94RU52RHFibDd4QktaVHRGNno2SE5FZDBJRVBLY0MxR0t4NllnWEZScUVwZFhVSmgrbnJYRUxvcHRJMnc3SE45ZUhqeE1HOWlUMzZoSmlqNnRhUTVsaXNPTnJHN1pZc2lxSG4yK1Z2NzVEYjRV*/
// This file was auto-generated from sdk-root/src/data/elastictranscoder/2012-09-25/waiters-2.json
return [ 'version' => 2, 'waiters' => [ 'JobComplete' => [ 'delay' => 30, 'operation' => 'ReadJob', 'maxAttempts' => 120, 'acceptors' => [ [ 'expected' => 'Complete', 'matcher' => 'path', 'state' => 'success', 'argument' => 'Job.Status', ], [ 'expected' => 'Canceled', 'matcher' => 'path', 'state' => 'failure', 'argument' => 'Job.Status', ], [ 'expected' => 'Error', 'matcher' => 'path', 'state' => 'failure', 'argument' => 'Job.Status', ], ], ], ],];