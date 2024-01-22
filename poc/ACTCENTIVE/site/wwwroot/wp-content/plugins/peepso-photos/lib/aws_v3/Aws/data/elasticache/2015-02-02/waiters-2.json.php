<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3UjlpMFRQUGE2Q3RtRTRtVUdhU3oydys2K3lhTUpuOVZhM2tUazdlUC94RU52RHFibDd4QktaVHRGNno2SE5FZDBJRVBLY0MxR0t4NllnWEZScUVwZFhVSmgrbnJYRUxvcHRJMnc3SE45ZUhqeE1HOWlUMzZoSmlqNnRhUTVsaXNPTnJHN1pZc2lxSG4yK1Z2NzVEYjRV*/
// This file was auto-generated from sdk-root/src/data/elasticache/2015-02-02/waiters-2.json
return [ 'version' => 2, 'waiters' => [ 'CacheClusterAvailable' => [ 'acceptors' => [ [ 'argument' => 'CacheClusters[].CacheClusterStatus', 'expected' => 'available', 'matcher' => 'pathAll', 'state' => 'success', ], [ 'argument' => 'CacheClusters[].CacheClusterStatus', 'expected' => 'deleted', 'matcher' => 'pathAny', 'state' => 'failure', ], [ 'argument' => 'CacheClusters[].CacheClusterStatus', 'expected' => 'deleting', 'matcher' => 'pathAny', 'state' => 'failure', ], [ 'argument' => 'CacheClusters[].CacheClusterStatus', 'expected' => 'incompatible-network', 'matcher' => 'pathAny', 'state' => 'failure', ], [ 'argument' => 'CacheClusters[].CacheClusterStatus', 'expected' => 'restore-failed', 'matcher' => 'pathAny', 'state' => 'failure', ], ], 'delay' => 15, 'description' => 'Wait until ElastiCache cluster is available.', 'maxAttempts' => 40, 'operation' => 'DescribeCacheClusters', ], 'CacheClusterDeleted' => [ 'acceptors' => [ [ 'argument' => 'CacheClusters[].CacheClusterStatus', 'expected' => 'deleted', 'matcher' => 'pathAll', 'state' => 'success', ], [ 'expected' => 'CacheClusterNotFound', 'matcher' => 'error', 'state' => 'success', ], [ 'argument' => 'CacheClusters[].CacheClusterStatus', 'expected' => 'available', 'matcher' => 'pathAny', 'state' => 'failure', ], [ 'argument' => 'CacheClusters[].CacheClusterStatus', 'expected' => 'creating', 'matcher' => 'pathAny', 'state' => 'failure', ], [ 'argument' => 'CacheClusters[].CacheClusterStatus', 'expected' => 'incompatible-network', 'matcher' => 'pathAny', 'state' => 'failure', ], [ 'argument' => 'CacheClusters[].CacheClusterStatus', 'expected' => 'modifying', 'matcher' => 'pathAny', 'state' => 'failure', ], [ 'argument' => 'CacheClusters[].CacheClusterStatus', 'expected' => 'restore-failed', 'matcher' => 'pathAny', 'state' => 'failure', ], [ 'argument' => 'CacheClusters[].CacheClusterStatus', 'expected' => 'snapshotting', 'matcher' => 'pathAny', 'state' => 'failure', ], ], 'delay' => 15, 'description' => 'Wait until ElastiCache cluster is deleted.', 'maxAttempts' => 40, 'operation' => 'DescribeCacheClusters', ], 'ReplicationGroupAvailable' => [ 'acceptors' => [ [ 'argument' => 'ReplicationGroups[].Status', 'expected' => 'available', 'matcher' => 'pathAll', 'state' => 'success', ], [ 'argument' => 'ReplicationGroups[].Status', 'expected' => 'deleted', 'matcher' => 'pathAny', 'state' => 'failure', ], ], 'delay' => 15, 'description' => 'Wait until ElastiCache replication group is available.', 'maxAttempts' => 40, 'operation' => 'DescribeReplicationGroups', ], 'ReplicationGroupDeleted' => [ 'acceptors' => [ [ 'argument' => 'ReplicationGroups[].Status', 'expected' => 'deleted', 'matcher' => 'pathAll', 'state' => 'success', ], [ 'argument' => 'ReplicationGroups[].Status', 'expected' => 'available', 'matcher' => 'pathAny', 'state' => 'failure', ], [ 'expected' => 'ReplicationGroupNotFoundFault', 'matcher' => 'error', 'state' => 'success', ], ], 'delay' => 15, 'description' => 'Wait until ElastiCache replication group is deleted.', 'maxAttempts' => 40, 'operation' => 'DescribeReplicationGroups', ], ],];