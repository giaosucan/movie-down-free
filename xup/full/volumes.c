/*cl volumes.c*/
#include <windows.h>
#define A MAX_PATH+1
main(){int a=GetLogicalDrives();char b=26,c[]="A:\\",d[A],e[A];
for(;b;a/=2,--b,++*c)if(a%2){
if(!GetVolumeInformation(c,d,A,0,0,0,e,A))*d=*e=0;
printf("%s|%u|%s|%s\n",c,GetDriveType(c),e,d);}}