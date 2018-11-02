#!/usr/bin/python
# -*- coding: utf-8 -*-
import glob, os, shutil, sys, io, itertools, fnmatch, filecmp, datetime, smtplib, zipfile
import pandas as pd
import numpy as np
from matplotlib import pyplot as plt
from time import time
from distutils.dir_util import copy_tree

from email.mime.text import MIMEText
from email.mime.multipart import MIMEMultipart
from email.mime.base import MIMEBase
from email import encoders

start = time()
right_now = datetime.datetime.now()

username = os.path.split(os.getcwd())[1]
stproj = os.path.split(os.path.split(os.getcwd())[0])[0]
recipient = sys.argv[1]
user_name = sys.argv[2]

results = os.path.join('results/')
if not os.path.exists(results):
    os.mkdir(results)

opening = open("output.php","w+")
opening.close()

prepend = open("output.php","r")
ugh = prepend.read()
prepend.close()

sys.stdout = open("output.php","w+")

print "<!DOCTYPE html>"
print "<html lang='en'><head><meta charset='utf-8'>"
print '<meta name="viewport" content="width=device-width, initial-scale=1.0">'
print '<style>body{font-family: Arial, Helvetica, sans-serif;font-size:15px;}</style>'
print "</head><body><h1>Results</h1>"
print right_now, "<br />"

body = "<!DOCTYPE html>"
body += "<html lang='en'><head><meta charset='utf-8'>"
body += '<meta name="viewport" content="width=device-width, initial-scale=1.0">'
body += '<style>body{font-family: Arial, Helvetica, sans-serif;font-size:15px;}</style>'
body += "</head><body>"
body += '<h2>Hi '+user_name+',<br /><br />These are your results:</h2>'

cwd_no_usrnm = os.path.dirname(os.getcwd())
shutil.copy(os.path.join(cwd_no_usrnm+'/seismology.R'), 'seismology.R')
shutil.copy(os.path.join(cwd_no_usrnm+'/utils.R'), 'utils.R')


######PERTURB and LEARN######################

to_perturb = os.getcwd()
to_exec = ("/usr/local/bin/Rscript {}/perturb.R 2>&1").format(to_perturb)
try:
    os.system(to_exec)
    print "perturbations succesful <br />"
    body += "perturbations successful <br />"
except:
    print "perturbations error <br />"
    body += "perturbations error <br />"
    print os.system('which Rscript')
'''

try:
    os.system("python learn.py")
    print "machine learning successful <br />"
except:
    print "machine learning stopped"
    print error
'''

#####ECHELLE and ORGANIZING FILES PER STAR############
filelist = glob.glob('data/*-freqs.dat')
tables = glob.glob('product/covs-simulations/perturb/data/*.dat')
pngs = glob.glob('product/plots-simulations/perturb/data/*.png')
pdfs = glob.glob('product/plots-simulations/perturb/data/*.pdf')
perturbs = glob.glob('perturb/*.dat')

print "<br />Large Frequency Separation (v mod):<br />"
body += "<br />Large Frequency Separation (v mod):<br />"
count_stars = 1

for filename in filelist:

    #identifying each star
    fname = filename[5:-10]

    #display folder for each star
    directory = os.path.join(results + fname + '/')
    if not os.path.exists(directory):
        os.mkdir(directory)

    #individual folder content
    for table in tables:
        if table[38:-4] == fname:
            tabledir = os.path.join(directory + table[38:])
            shutil.copy(table, tabledir)
    for png in pngs:
        if png[39:-4] == fname:
            pngdir = os.path.join(directory + png[39:])
            shutil.copy(png, pngdir)
    for pdf in pdfs:
        if pdf[39:-4] == fname:
            pdfdir = os.path.join(directory + pdf[39:])
            shutil.copy(pdf, pdfdir)
    for perturb in perturbs:
        if perturb[8:-12] == fname:
            perturbdir = os.path.join(directory + perturb[8:])
            shutil.copy(perturb, perturbdir)

    #calculate freq mod
    countZero = 0
    data = open(filename)
    efile = open('filetool/echelle.txt', 'w+')
    data_1 = open('filetool/data_1.txt', 'w+')
    data_2 = open('filetool/data_2.txt', 'w+')
    data_3 = open('filetool/data_3.txt', 'w+')

    lines = data.readlines()
    countZero = 0

    for line in lines:
        if line.find(" 0 ") != -1:
            countZero += 1
            efile.write(line)
        elif line.find(" 1 ") != -1:
            countZero += 1
            data_1.write(line)
        elif line.find(" 2 ") != -1:
            countZero += 1
            data_2.write(line)
        elif line.find(" 3 ") != -1:
            countZero += 1
            data_3.write(line)

    efile.close()
    data_1.close()
    data_2.close()
    data_3.close()

    efile = open('filetool/echelle.txt', 'r')
    l = np.loadtxt(efile)
    efile.close()
    array = np.array(l[:,2])
    diff_array = np.ediff1d(array)
    freq_mod = np.mean(diff_array)

    #individual plotting echelle
    plt.figure(count_stars)
    plt.title('Echelle '+fname, fontsize=25)
    plt.xlabel ('frequecy modulo large separation (uHz)', fontsize=20)
    plt.ylabel ('cyclic frequency (uHz)', fontsize=20)
    plt.tick_params(axis='both', which='major', labelsize=15)
    plt.tick_params(axis='both', which='minor', labelsize=15)

    data_0 = 'filetool/echelle.txt'
    if os.path.getsize(data_0) > 0:
        freqs_0 = np.loadtxt(open(data_0), skiprows =1)
        plt.plot((freqs_0[:,2])%freq_mod,freqs_0[:,2],'bh')
        plt.legend (['l=0'])

    data_1 = 'filetool/data_1.txt'
    if os.path.getsize(data_1) > 0:
        freqs_1 = np.loadtxt(open(data_1), skiprows =1)
        plt.plot((freqs_1[:,2])%freq_mod,freqs_1[:,2],'ro')
        plt.legend (['l=0','l=1'])

    data_2 = 'filetool/data_2.txt'
    if os.path.getsize(data_2) > 0:
        freqs_2 = np.loadtxt(open(data_2), skiprows =1)
        plt.plot((freqs_2[:,2])%freq_mod,freqs_2[:,2],'cd')
        plt.legend (['l=0','l=1','l=2'])

    data_3 = 'filetool/data_3.txt'
    if os.path.getsize(data_3) > 0:
        freqs_3 = np.loadtxt(open(data_3), skiprows =1)
        plt.plot((freqs_3[:,2])%freq_mod,freqs_3[:,2],'y^')
        plt.legend (['l=0','l=1','l=2','l=3'])

    plt.savefig(os.path.join(directory + filename[5:-10] + "-echelle.png"))
    count_stars += 1
    print filename[5:-10], "- ", freq_mod, "<br />"
    body += str(filename[5:-10])+"- "+str(freq_mod)+"<br />"


###########ALGORITHM PRINTOUT###################

f = open('filetool/table.txt', 'w+')
top = open('filetool/results.txt', 'w+')
bot = open('filetool/resultsbottom.txt', 'w+')

counting_stars = len(glob.glob("product/covs-simulations/perturb/data/*"))

with open('filetool/product.txt', 'r') as proddu:
    for feature_ranking in itertools.islice(proddu, 0, 13):
        top.write(feature_ranking)
        print '<br />' + feature_ranking
        body += '<br />' + feature_ranking
top.close()

with open('filetool/product.txt', 'r') as prodd:
    for line_text in itertools.islice(prodd, 13, 15+counting_stars):
        f.write(line_text)
f.close()

with open('filetool/product.txt', 'r') as proddub:
    for bottom in itertools.islice(proddub, 14+counting_stars, 21+counting_stars):
        bot.write(bottom)
        print bottom + '<br   />'
        body += bottom + '<br   />'
bot.close()

df = pd.read_csv('filetool/table.txt', delim_whitespace=True, engine='python', header='infer')
table = df.to_html()

with io.open('product.html', 'w+', encoding='utf-8') as lol:
    lol.write(table)


###############RE ORGANIZING RESULTS###############################

init = os.path.join('product', 'tables-simulations', 'data_init.dat')
initdir = os.path.join(results + init[32:])

if os.path.isfile(initdir):
    if not filecmp.cmp(init, initdir):
        shutil.copy(init, initdir)
else:
    shutil.copy(init, initdir)

curr = os.path.join('product', 'tables-simulations', 'data_curr.dat')
currdir = os.path.join(results + curr[32:])

if os.path.isfile(currdir):
    if not filecmp.cmp(curr, currdir):
        shutil.copy(curr, currdir)
else:
    shutil.copy(curr, currdir)

feat = os.path.join(results + 'feature-importance/')

if not os.path.exists(feat):
    os.mkdir(feat)

covs = os.path.join('product', 'covs-simulations', 'feature-importance-data.dat')
covsdir = os.path.join(feat + covs[25:-9] + '.dat')

if os.path.isfile(covsdir):
    if not filecmp.cmp(covs, covsdir):
        shutil.copy(covs, covsdir)
else:
    shutil.copy(covs, covsdir)

plots = os.path.join('product', 'plots-simulations', 'feature_importance-data.pdf')
plotsdir = os.path.join(feat + plots[26:-9] + '.pdf')

if os.path.isfile(plotsdir):
    if not filecmp.cmp(plots, plotsdir):
        shutil.copy(plots, plotsdir)
else:
    shutil.copy(plots, plotsdir)


end = time()
print '<br /><br />'
print (end - start), "seconds"
print "<div style='margin-top:20px;float:center;'><h2>Parameters</h2>"
print '<iframe style="border:0;margin-top:10px; width:100%;height:20%;overflow:auto" src="product.html"></iframe>'
print "</body>"
print "<style>body{margin-left:3%;}</style></html>"
print
print ugh
print
sys.stdout.close()
body += '<br /><br />' + str(end - start) + " seconds"
body += "<div style='margin-top:20px;float:center;'><h1>Parameters</h1>"
table_print = open("product.html","r")
product_html = table_print.read()
table_print.close()
body += product_html
body += "<br /><br /></body><style>body{margin-left:3%;}</style></html>"
################## MAKE FILES ZIP ##############################

def zipp(directory, tozip):
    for root, dir, files in os.walk(directory):
        for file in files:
            tozip.write(os.path.join(root, file))

if __name__ == '__main__':
    tozip = zipfile.ZipFile('results-'+username+'.zip', 'w', zipfile.ZIP_DEFLATED)
    zipp(results, tozip)
    tozip.close()

#################### SEND THE RESULTS ###########################
#credit https://docs.python.org/2/library/email-examples.html
#credit https://docs.python.org/2/library/email.mime.html

#set any account (gmail)
gmail = 'alecita9perea@gmail.com'
password = 'highschool19'
results_file ='results-'+user_name+'.zip'

msg = MIMEMultipart()
msg['From'] = gmail
msg['To'] = recipient
msg['Subject'] = "Starithm Results"
msg.attach(MIMEText(body,'html'))

attachment = open(results_file,'r')
part = MIMEBase('application','octet-stream')
part.set_payload((attachment).read())
encoders.encode_base64(part)
part.add_header('Content-Disposition',"attachment;filename= "+results_file)
msg.attach(part)

text = msg.as_string()
server = smtplib.SMTP('smtp.gmail.com',587) #server and port number
server.starttls()
server.login(gmail,password) #for this gmail -> Allow first secure APPs (so it's not blocked)
server.sendmail(gmail,recipient,text)
server.quit()

#######save the stars############

star_db_loc = os.path.join(stproj+'/stars/')
if not os.path.exists(star_db_loc):
    os.mkdir(star_db_loc)

stars = glob.glob('results/*')
feature = os.path.join(results + 'feature-importance')

for star in stars:
    if feature != star and initdir != star and currdir != star:
        star_db = os.path.join(star_db_loc + star[8:])
        if not os.path.exists(star_db):
            copy_tree(star, star_db)

###################################
