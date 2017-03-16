import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

import { RegisterLangSelectComponent } from './registerLangSelect.component';
import { RegisterLangSelectRoutingModule } from './registerLangSelect-routing.module';
import { AuthService } from '../../shared/services/auth.service';

@NgModule({
  imports: [CommonModule, RegisterLangSelectRoutingModule, FormsModule],
  declarations: [RegisterLangSelectComponent],
  exports: [RegisterLangSelectComponent],
  providers: [AuthService]
})
export class RegisterLangSelectModule { }