(function($)
{
    class notice {
        constructor(cpt, el, col, row, nb, mrg) {
            this.n_top = 0;
            this.n_left = 0;
            this.n_number = cpt;
            this.n_nbcol = nb;
            this.n_row = row;
            this.n_column = col;
            this.n_div = el;
            this.n_height = el.height();
            this.n_width = el.width();
            this.marge = mrg;
        }

        defineplace(tabnot) {
            let beforecol = tabnot[this.n_number - 1]
            let beforediv = tabnot[this.n_number - this.n_nbcol]
            if (this.n_column === 0) {
                this.n_left = this.marge;
            } else {
                this.n_left = beforecol.n_left + this.n_width + this.marge;
            }
            if (this.n_row === 0) {
                this.n_top = 0;
            } else {
                this.n_top = beforediv.n_top + beforediv.n_height + 30;
            }
            this.n_div.css('left', this.n_left + 'px')
            this.n_div.css('top', this.n_top + 'px')
            this.n_div.show()
            return this.n_height;
        }
    }

    $.fn.placernotice=function(listpost) {
        let div_w = $(this).width();
        let col_w = listpost.width();
        let nbcol = Math.trunc(div_w / (col_w + 10))
        let mrg = (div_w - (col_w * nbcol)) / (nbcol + 1)
        let heightall = 0;
        let tabnot = [];
        let cpt = 0;
        let nb_el = listpost.length
        let nbtour = Math.trunc(nb_el / nbcol)
        let toursup = nb_el % nbcol
        if (toursup !== 0) nbtour++;
        let indexd = 0;


        for (let row = 0; row <= nbtour - 1; row++) {
            let tabslice = []
            if (indexd + nbcol < nb_el) {
                tabslice = listpost.slice(indexd, indexd + nbcol)
            } else {
                tabslice = listpost.slice(indexd)
            }
            indexd = indexd + nbcol
            let col = 0;
            tabslice.each(function (index) {

                tabnot[cpt] = new notice(cpt, $(this), col, row, nbcol, mrg);
                cpt++
                col++
            })
        }
        tabnot.forEach(function (elem) {
            heightall += elem.defineplace(tabnot)
        })
       // $(this).css('height', tabnot[tabnot.length - 1].n_top + tabnot[tabnot.length - 1].n_height + 50 + 'px');

        // version avec valeur max de la notice 540px + marge de 50 soit total ajouter au top  de 590 px
        $(this).css('height', tabnot[tabnot.length - 1].n_top + 590 + 'px');
        return $(this);
    }

})(jQuery);