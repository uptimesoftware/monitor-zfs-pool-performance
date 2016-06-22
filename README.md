ZFS Pool Performance Monitor
============================
The ZFS Pool Performance plugin leverages the `/usr/sbin/zpool iostat -v` command in Solaris to collect ZFS Pool metrics.  The plugin returns the following metrics:

Capacity Allocated (MB)
Capacity Free (MB)
Operations Read 
Operations Write 
Read Bandwidth (MB)
Write Bandwidth (MB)
