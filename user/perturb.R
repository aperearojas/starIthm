#! /usr/bin/Rscript
#### Generate Monte-Carlo perturbations of observed stars
#### Author: Earl Bellinger ( bellinger@mps.mpg.de )
#### Stellar predictions & Galactic Evolution Group
#### Max-Planck-Institut fur Sonnensystemforschung

### put seismology into tools and worked also (changed) 1 with data folder testing now

getwd()
source(file.path('seismology.R'))
library(parallel)
library(parallelMap)

#options(warn=1)

dir.create('perturb', showWarnings=FALSE)

speed_of_light = 299792 # km/s
n_perturbations = 100

### Obtain properties of real stars varied within their uncertainties
perturb <- function(star, obs_data_file, freqs_data_file, n_perturbations) {
  obs_data <<- read.table(obs_data_file, header=TRUE)
  freqs <<- read.table(freqs_data_file, header=TRUE)
  nu_max <<- obs_data[obs_data$name == 'nu_max',]$value
  seis.DF <- seismology(freqs, nu_max)#, outf=star, filepath=file.path('plots', 'perturb'))
  cols <<- length(seis.DF)
  start.time <- proc.time()
  res <- do.call(plyr:::rbind.fill, parallelMap(rand_inst, 1:n_perturbations))
  total.time <- proc.time() - start.time
  time_per_perturb <- total.time[[3]]/n_perturbations
  print(paste("Total time:", total.time[[3]],
              "; Time per perturbation:", time_per_perturb))
  return(list(res=res, time_per_perturb=time_per_perturb))
}

rand_inst <- function(n) {
  # Perturb observations by their uncertainties
  attach(obs_data)
  repeat {
    obs.DF <- data.frame(rbind(rnorm(nrow(obs_data), value,
                                     if (n==1) 0 else uncertainty)))
    colnames(obs.DF) <- name

    # Correct frequencies for Doppler shift
    radial_velocity <-
      if (any(grepl("radial_velocity", obs_data$name))) {
        rnorm(1, value[name=="radial_velocity"],
              ifelse(n==1, 0, uncertainty[name=="radial_velocity"]))
      } else 0
    doppler_beta <- radial_velocity/speed_of_light
    doppler_shift <- sqrt((1+doppler_beta)/(1-doppler_beta))

    # Perturb frequencies
    noisy_freqs <- freqs
    noisy_freqs$nu <- rnorm(nrow(freqs), freqs$nu * doppler_shift,
                            ifelse(n==1, 0, freqs$dnu * doppler_shift))
    # Calculate Dnu, dnus, and ratios
    seis.DF <- seismology(noisy_freqs, obs.DF$nu_max)
    if (all(!is.na(seis.DF)) && length(seis.DF) == cols) break
    #if (length(seis.DF) == cols) break
  }
  detach(obs_data)
  merge(rbind(obs.DF), rbind(seis.DF))
}

process_dir <- function(star_dir) {
  out_dir <- file.path('perturb', basename(star_dir))      ##added 'data'(changed)
  dir.create(out_dir, showWarnings=FALSE)
  fnames <- list.files(star_dir)
  times <- c()
  for (fname in fnames) {
    if (!grepl('-obs.dat', fname)) next
    star <- sub('-obs.dat', '', fname)
    times <- c(times, process_star(star, star_dir, out_dir=out_dir))
  }
  print(paste("Average time:", mean(times), "+/-", sqrt(var(times))))
  cat('\n\n')
}

process_star <- function(star, star_dir, out_dir="perturb") {
  print(paste("Processing", star))
  obs_data_file <- file.path(star_dir, paste0(star, "-obs.dat"))
  freqs_data_file <- file.path(star_dir, paste0(star, "-freqs.dat"))
  results <- perturb(star, obs_data_file, freqs_data_file, n_perturbations)
  result <- results$res
  if (!is.null(result)) {
    write.table(result,
                file.path(out_dir, paste0(star, "_perturb.dat")),
                quote=FALSE, sep='\t', row.names=FALSE)
  }
  results$time_per_perturb
}

## Perturb every star 10k times and save the results
#parallelStartMulticore(max(1,as.integer(Sys.getenv()[['OMP_NUM_THREADS']])))
#process_dir(file.path("data", "legacyRox2"))
#stop()
#process_dir(file.path("data", "legacyRox"))
#process_dir(file.path("data", "sg-hares-mesa"))
#process_dir(file.path("data", "sg-hares"))
#process_dir(file.path("data", "procyon"))
#process_dir(file.path("data", "Dnu"))
#process_dir(file.path("data", "legacy"))
#process_dir(file.path("data", "kages"))
#process_dir(file.path("data", "basu"))
#process_dir(file.path("data", "hares"))

#star_names <- commandArgs()
#print(star_names)
#star_dir <- file.path("data")
#for (star in list.files(star_dir)) process_star(star, star_dir)
args <- commandArgs(TRUE)
N <- args[1]
args[1] <- process_dir("data")
