
		var NumberOfWords = 40

		var words = new BuildArray(NumberOfWords)

		words[1] = "THOMAS EDISON"
		words[2] = "LEONARDO DA VINCI"
		words[3] = "NICOLA TESLA"
		words[4] = "WILHELM CONRAD RONTGEN"
		words[5] = "HENDRIK LORENTZ"
		words[6] = "PIETER ZEEMAN"
		words[7] = "MARIE CURIE"
		words[8] = "ISAAC ASIMOV"
		words[9] = "MAX PLANCK"
		words[10] = "JOHANNES STARK"
		words[11] = "NIELS BOHR"
		words[12] = "GUSTAV HERTZ"
		words[13] = "JAMES FRANCK"
		words[14] = "LOUIS DE BROGLIE"
		words[15] = "CHANDRASEKHARA VENKATA RAMAN"
		words[16] = "WERNER HEISENBERG"
		words[17] = "ERWIN SCHRODINGER"
		words[18] = "ALAN TURING"
		words[19] = "JOHN VON NEUMANN"
		words[20] = "WILLIAM ECCLES"
		words[21] = "WILLIAM SHOCKLEY"
		words[22] = "WALTER BRATTAIN"
		words[23] = "JOHN BARDEEN"
		words[24] = "JAMES PRESCOTT JOULE"
		words[25] = "LEON FOUCAULT"
		words[26] = "JOSEPH HENRY"
		words[27] = "WILHELM EDUARD WEBER"
		words[28] = "WERNER VON SIEMENS"
		words[29] = "JAMES WATT"
		words[30] = "ANDRE-MARIE AMPERE"
		words[31] = "CHARLES COULOMB"
		words[32] = "HANS ORSTED"
		words[33] = "JAMES MAXWELL"
		words[34] = "CLAUDE POUILLET"
		words[35] = "JOSEPH FOURIER"
		words[36] = "ALESSANDRO VOLTA"
		words[37] = "ALBERT EINSTEIN"
		words[38] = "MICHAEL FARADAY"
		words[39] = "GEORGE SIMON OHM"
		words[40] = "ENRICO FERMI"

		function BuildArray(size){
			this.length = size
			for (var i = 1; i <= size; i++){
			this[i] = null}
			return this
		}

		function randword(frm) {
		// rand number generators
			var rnd = Math.ceil(Math.random() * NumberOfWords)

		// show the word
			document.getElementById("randomly").innerHTML = words[rnd]
			delay = (8000)
			setTimeout(randword, delay);

			$("#randomly").fadeIn(delay/2);
			$("#randomly").fadeOut(delay/2);

			var NumProject = 3;
			var NumChaos = 5;
			var NumTeardown = 1;
			var NumPCB = 62;

			var element = document.getElementById("stats");
			element.innerHTML = "<p><u><b>Stats:</b></u></p></br><ul><li>- Projects: " + NumProject + "</li><li>- Chaos Articles: " + NumChaos + "</li><li>- Teardowns: " + NumTeardown + "</li><li>- PCB's designed: " + NumPCB + "</li></ul> ";
		}

