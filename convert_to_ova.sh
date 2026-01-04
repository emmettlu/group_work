#!/bin/sh
docker pull linkacloud/d2vm:latest
alias d2vm='sudo docker run --rm -it --privileged -v /var/run/docker.sock:/var/run/docker.sock -v $(pwd):/work -w /work linkacloud/d2vm:latest'

# Get qcow2 file
d2vm convert vulhub/samba:4.6.3 -o vm.qcow2 --size 10G -p passwd --bootloader grub --network-manager ifupdown -v

# Get vmdk file
sudo qemu-img convert -f qcow2 -O vmdk vm.qcow2 vm.vmdk

# Get ova file
./ovftool/ovftool vm.vmdk vm.ova
